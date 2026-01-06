<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Journal;
use Illuminate\Http\Client\ConnectionException;

class RecapController extends Controller
{
    private $messages = [
        'missing_api_key' => [
            'id' => 'Konfigurasi API Key Gemini tidak ditemukan.',
            'en' => 'Gemini API key configuration not found.',
        ],
        'forbidden' => [
            'id' => 'Akses ditolak. Beberapa jurnal tidak dapat ditemukan.',
            'en' => 'Access denied. Some journals could not be found.',
        ],
        'ai_failed' => [
            'id' => 'Gagal berkomunikasi dengan layanan AI.',
            'en' => 'Failed to communicate with AI service.',
        ],
        'recap_success' => [
            'id' => 'Ringkasan berhasil dibuat.',
            'en' => 'Recap generated successfully.',
        ],
        'no_recap' => [
            'id' => 'Tidak dapat menghasilkan ringkasan saat ini.',
            'en' => 'Unable to generate recap at this time.',
        ],
    ];

    private function msg($key)
    {
        $lang = request()->query('lang', 'id');
        return $this->messages[$key][$lang] ?? $this->messages[$key]['id'];
    }

    public function generateRecap(Request $request)
    {
        $validated = $request->validate([
            'journal_ids' => 'required|array|min:3',
            'journal_ids.*' => 'integer|exists:journals,id',
        ]);

        $lang = $request->query('lang', 'id');
        $user = $request->user();
        $journalIds = $validated['journal_ids'];

        $journals = Journal::where('user_id', $user->id)
            ->whereIn('id', $journalIds)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($journals->count() !== count($journalIds)) {
            return response()->json(['message' => $this->msg('forbidden')], 403);
        }

        $prompt = $this->buildPrompt($journals, $lang);

        // Prefer GROQ API key (openai-compatible) if present, otherwise fall back to Gemini key
        $groqKey = env('GROQ_API_KEY');
        $geminiKey = env('GEMINI_API_KEY');

        if (!$groqKey && !$geminiKey) {
            return response()->json(['message' => $this->msg('missing_api_key')], 500);
        }

        // If GROQ key is available, use Groq / OpenAI-compatible Responses API
        if ($groqKey) {
            try {
                // Use Groq Chat Completions endpoint with messages (more natural for conversation/chat)
                $payload = [
                    'model' => 'openai/gpt-oss-120b',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    // tuning knobs — adjust as needed
                    'temperature' => 0.7,
                    'max_tokens' => 800,
                ];

                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$groqKey}",
                    'Content-Type' => 'application/json',
                ])->timeout(60)
                    ->retry(3, 1000)
                    ->post('https://api.groq.com/openai/v1/chat/completions', $payload);
            } catch (ConnectionException $e) {
                return response()->json([
                    'message' => $this->msg('ai_failed') . ' (' . $e->getMessage() . ')'
                ], 502);
            }

            if ($response->failed()) {
                return response()->json(['message' => $this->msg('ai_failed')], 502);
            }

            $body = $response->json();

            // Groq Chat Completions typically returns choices[].message.content
            // Fallback to output_text or legacy shapes if necessary
            $recap = $body['choices'][0]['message']['content'] ?? $body['output_text'] ?? ($body['output'][0]['content'][0]['text'] ?? null) ?? $this->msg('no_recap');
        } else {
            // Fallback: use Gemini (Google) if GROQ key not provided
            try {
                $response = Http::timeout(30)
                    ->retry(3, 1000)
                    ->post(
                        "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$geminiKey}",
                        [
                            'contents' => [
                                [
                                    'parts' => [
                                        ['text' => $prompt]
                                    ]
                                ]
                            ]
                        ]
                    );
            } catch (ConnectionException $e) {
                return response()->json([
                    'message' => $this->msg('ai_failed') . ' (' . $e->getMessage() . ')'
                ], 502);
            }

            if ($response->failed()) {
                return response()->json(['message' => $this->msg('ai_failed')], 502);
            }

            $recap = $response->json()['candidates'][0]['content']['parts'][0]['text']
                ?? $this->msg('no_recap');
        }

        $recap = preg_replace('/\s+/', ' ', trim($recap));

        return response()->json([
            'message' => $this->msg('recap_success'),
            'lang' => $lang,
            'recap' => $recap
        ]);
    }

    private function buildPrompt($journals, $lang): string
    {
        $prompt = $lang === 'id'
            ? "Kamu adalah asisten refleksi diri yang bijaksana. Bacalah beberapa entri jurnal berikut dari pengguna, lalu buat ringkasan singkat dalam bahasa Indonesia yang hangat dan positif. Ringkasan harus maksimal 100 kata dan menggambarkan perasaan, pola pikir, atau perkembangan dari pengguna.\n\nBerikut entri jurnalnya:\n\n"
            : "You are a wise self-reflection assistant. Read several journal entries below from a user, then create a short recap in English that is warm and supportive. The recap should be under 100 words and summarize the emotions, mindset, or progress shown.\n\nHere are the journal entries:\n\n";

        foreach ($journals as $journal) {
            $prompt .= "---\n";
            $prompt .= ($lang === 'id' ? "Tanggal: " : "Date: ") . $journal->created_at->format('d F Y') . "\n";
            $prompt .= "Mood: " . $journal->mood . "\n";

            if (is_array($journal->answers)) {
                foreach ($journal->answers as $answer) {
                    $question = $answer['question'] ?? '';
                    $answerText = $answer['answer'] ?? '';
                    if ($question || $answerText) {
                        $prompt .= ($lang === 'id' ? "Tanya: " : "Q: ") . $question . "\n";
                        $prompt .= ($lang === 'id' ? "Jawab: " : "A: ") . $answerText . "\n";
                    }
                }
            }
        }

        $prompt .= "---\n\n";

        // Add explicit instructions to improve output quality for Groq/OpenAI-like models
        if ($lang === 'id') {
            $prompt .= "Instruksi untuk model: Buat ringkasan singkat dalam bahasa Indonesia yang hangat, empatik, dan positif. "
                . "Panjang ringkasan maksimal 100 kata. Jangan menambahkan informasi yang tidak ada di entri jurnal. "
                . "Gunakan kalimat sederhana, fokus pada emosi, pola pikir, atau perkembangan pengguna. "
                . "Keluarkan hanya teks ringkasan dan kemudian 2–3 poin utama (Highlights) — setiap poin 3–7 kata — diawali baris 'Highlights:'. "
                . "Jika tidak memungkinkan untuk membuat ringkasan, keluarkan persis: '" . $this->msg('no_recap') . "'.";
        } else {
            $prompt .= "Instructions for the model: Create a short recap in English that is warm, empathetic, and positive. "
                . "Keep the recap under 100 words. Do not add information not present in the journal entries. "
                . "Use simple sentences and focus on emotions, mindset, or progress. "
                . "Output only the recap text followed by 2–3 short highlight points (3–7 words each) prefixed by a line 'Highlights:'. "
                . "If you cannot produce a recap, output exactly: '" . $this->msg('no_recap') . "'.";
        }

        return $prompt;
    }
}
