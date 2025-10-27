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
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json(['message' => $this->msg('missing_api_key')], 500);
        }

        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}",
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
        $prompt .= $lang === 'id'
            ? "Sekarang buatkan ringkasannya dalam bahasa Indonesia."
            : "Now, generate the recap in English.";

        return $prompt;
    }
}
