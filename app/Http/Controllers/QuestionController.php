<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    private $messages = [
        'success' => [
            'id' => 'Pertanyaan berhasil diambil.',
            'en' => 'Questions retrieved successfully.',
        ],
        'invalid_mood' => [
            'id' => 'Mood yang dipilih tidak valid.',
            'en' => 'Invalid mood selected.',
        ],
    ];

    private function msg($key)
    {
        $lang = request()->query('lang', 'id');
        return $this->messages[$key][$lang] ?? $this->messages[$key]['id'];
    }

    public function getQuestionsByMood(Request $request)
    {
        $validMoods = ['Happy', 'Grateful', 'Sad', 'Angry'];

        $validated = $request->validate([
            'mood' => ['required', 'string', Rule::in($validMoods)],
        ]);

        $lang = $request->query('lang', 'id'); 

        $questionsByMoodEn = [
            'Happy' => [
                "What made you smile today?",
                "Who or what are you grateful for right now?",
                "What small thing brightened your day?",
                "What's one accomplishment you're proud of today?",
            ],
            'Grateful' => [
                "Who is someone you're thankful for today, and why?",
                "What is a simple pleasure you enjoyed recently?",
                "What is something about your life right now that you feel grateful for?",
                "Describe a past challenge that you're now grateful for.",
            ],
            'Sad' => [
                "What's weighing on your mind right now?",
                "Is there anything you can do to be kind to yourself today?",
                "What's a small step you could take to feel a little better?",
                "Who could you reach out to for support?",
            ],
            'Angry' => [
                "What triggered this feeling of anger?",
                "What is this situation teaching you?",
                "How can you channel this energy in a productive way?",
                "What would a calm and collected response look like?",
            ],
        ];

        $questionsByMoodId = [
            'Happy' => [
                "Apa yang membuatmu tersenyum hari ini?",
                "Siapa atau apa yang kamu syukuri hari ini?",
                "Hal kecil apa yang membuat harimu lebih cerah?",
                "Pencapaian apa yang kamu banggakan hari ini?",
            ],
            'Grateful' => [
                "Siapa yang kamu syukuri hari ini, dan kenapa?",
                "Kenikmatan sederhana apa yang baru-baru ini kamu nikmati?",
                "Hal apa dari hidupmu sekarang yang paling kamu syukuri?",
                "Ceritakan tantangan masa lalu yang kini kamu syukuri.",
            ],
            'Sad' => [
                "Apa yang membuatmu sedih hari ini?",
                "Apa yang bisa kamu lakukan untuk bersikap lebih lembut pada dirimu sendiri?",
                "Langkah kecil apa yang bisa membuatmu merasa lebih baik?",
                "Siapa yang bisa kamu hubungi untuk meminta dukungan?",
            ],
            'Angry' => [
                "Apa yang memicu rasa marah ini?",
                "Apa yang bisa kamu pelajari dari situasi ini?",
                "Bagaimana kamu bisa menyalurkan energi ini dengan cara yang produktif?",
                "Seperti apa respons yang tenang dan bijak dalam situasi ini?",
            ],
        ];

        $questionsByMood = $lang === 'en' ? $questionsByMoodEn : $questionsByMoodId;

        $mood = $validated['mood'];
        $questions = $questionsByMood[$mood] ?? [];

        if (empty($questions)) {
            return response()->json([
                'message' => $this->msg('invalid_mood'),
                'data' => [],
            ], 400);
        }

        return response()->json([
            'message' => $this->msg('success'),
            'data' => [
                'lang' => $lang,
                'mood' => $mood,
                'questions' => $questions,
            ],
        ]);
    }
}
