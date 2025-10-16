<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    private $messages = [
        'feedback_success' => [
            'id' => 'Terima kasih atas feedback Anda!',
            'en' => 'Thank you for your feedback!',
        ],
        'feedback_top' => [
            'id' => 'Top 5 feedback berhasil diambil.',
            'en' => 'Top 5 feedback retrieved successfully.',
        ],
        'validation_error' => [
            'id' => 'Data yang dikirim tidak valid.',
            'en' => 'The submitted data is invalid.',
        ],
    ];

    private function msg($key)
    {
        $lang = request()->query('lang', 'id'); 
        return $this->messages[$key][$lang] ?? $this->messages[$key]['id'];
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => $this->msg('validation_error'),
                'errors' => $e->errors(),
            ], 422);
        }

        $feedback = Feedback::create([
            'user_id' => Auth::id(),
            'name' => $validatedData['name'],
            'rating' => $validatedData['rating'],
            'comment' => $validatedData['comment'],
        ]);

        return response()->json([
            'message' => $this->msg('feedback_success'),
            'data' => $feedback,
        ], 201);
    }

    public function getTopFeedback()
    {
        $topFeedbacks = Feedback::orderBy('rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'message' => $this->msg('feedback_top'),
            'data' => $topFeedbacks,
        ]);
    }
}
