<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Journal;

class AdminController extends Controller
{

    private $messages = [
        'success' => [
            'id' => 'Data jurnal berhasil diambil.',
            'en' => 'Journal data retrieved successfully.',
        ],
        'empty' => [
            'id' => 'Tidak ada data jurnal yang ditemukan.',
            'en' => 'No journal data found.',
        ],
    ];

    private function msg($key)
    {
        $lang = request()->query('lang', 'id');
        return $this->messages[$key][$lang] ?? $this->messages[$key]['id'];
    }

    public function getAllJournals(Request $request)
    {
        $journals = Journal::with('user')
            ->latest()
            ->paginate(15);

        if ($journals->isEmpty()) {
            return response()->json([
                'message' => $this->msg('empty'),
                'data' => [],
            ], 200);
        }

        $formattedData = $journals->through(function ($journal) {
            $answersText = collect($journal->answers)
                ->pluck('answer')
                ->implode(', ');

            $user = $journal->user;

            return [
                'tanggal_input' => $journal->created_at->format('Y-m-d H:i:s'),
                'tanggal_lahir' => $user && $user->birth_date ? $user->birth_date->format('Y-m-d') : null,
                'inputan' => $answersText,
                'mood' => $journal->mood,
            ];
        });

        return response()->json([
            'message' => $this->msg('success'),
            'data' => $formattedData,
        ], 200);
    }
}
