<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Journal;
use Carbon\Carbon;

class JournalController extends Controller
{

    private $messages = [
        'limit_reached' => [
            'id' => 'Anda telah mencapai batas maksimal 5 jurnal untuk hari ini.',
            'en' => 'You have reached the daily limit of 5 journals.',
        ],
        'add_success' => [
            'id' => 'Jurnal berhasil ditambahkan.',
            'en' => 'Journal entry added successfully.',
        ],
        'not_found' => [
            'id' => 'Jurnal tidak ditemukan.',
            'en' => 'Journal not found.',
        ],
        'delete_success' => [
            'id' => 'Jurnal berhasil dihapus.',
            'en' => 'Journal deleted successfully.',
        ],
        'trashed_success' => [
            'id' => 'Daftar jurnal yang diarsip berhasil diambil.',
            'en' => 'Archived journals retrieved successfully.',
        ],
        'restore_success' => [
            'id' => 'Jurnal berhasil dikembalikan dari arsip.',
            'en' => 'Journal restored successfully.',
        ],
        'archived_not_found' => [
            'id' => 'Jurnal di arsip tidak ditemukan.',
            'en' => 'Archived journal not found.',
        ],
        'stats_success' => [
            'id' => 'Statistik jurnal berhasil diambil.',
            'en' => 'Journal statistics retrieved successfully.',
        ],
        'detail_success' => [
            'id' => 'Detail jurnal berhasil diambil.',
            'en' => 'Journal details retrieved successfully.',
        ],
    ];

    // Helper buat ambil teks sesuai bahasa
    private function msg($key)
    {
        $lang = request()->query('lang', 'id');
        return $this->messages[$key][$lang] ?? $this->messages[$key]['id'];
    }

    // Get semua jurnal aktif
    public function index(Request $request)
    {
        $user = $request->user();
        $journals = Journal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return $journals;
    }

    // Get semua jurnal termasuk yang dihapus (arsip)
    public function allWithTrashed(Request $request)
    {
        $user = $request->user();
        $allJournals = Journal::where('user_id', $user->id)
            ->withTrashed()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return $allJournals;
    }

    // Tambah jurnal baru
    public function store(Request $request)
    {
        $user = $request->user();

        $journalCount = Journal::where('user_id', $user->id)->count();
        $journalCountToday = Journal::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->count();

        if ($journalCountToday >= 5) {
            return response()->json([
                'message' => $this->msg('limit_reached'),
            ], 429);
        }

        $validatedData = $request->validate([
            'mood' => 'required|string|max:50',
            'answers' => 'required|array',
            'answers.*.question' => 'required|string',
            'answers.*.answer' => 'required|string',
        ]);

        $journal = Journal::create([
            'user_id' => $user->id,
            'mood' => $validatedData['mood'],
            'answers' => $validatedData['answers'],
        ]);

        return response()->json([
            'message' => $this->msg('add_success'),
            'data' => $journal,
            'show_feedback_prompt' => ($journalCount === 0)
        ], 201);
    }

    // Lihat detail jurnal
    public function show(Request $request, $id)
    {
        $journal = Journal::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$journal) {
            return response()->json(['message' => $this->msg('not_found')], 404);
        }

        return response()->json([
            'message' => $this->msg('detail_success'),
            'data' => $journal,
        ]);
    }

    // Hapus (soft delete) jurnal
    public function destroy(Request $request, $id)
    {
        $journal = Journal::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$journal) {
            return response()->json(['message' => $this->msg('not_found')], 404);
        }

        $journal->delete();

        return response()->json(['message' => $this->msg('delete_success')]);
    }

    // Ambil daftar jurnal di arsip
    public function trashed(Request $request)
    {
        $trashedJournals = Journal::where('user_id', $request->user()->id)
            ->onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return response()->json([
            'message' => $this->msg('trashed_success'),
            'data' => $trashedJournals,
        ]);
    }

    // Kembalikan jurnal dari arsip
    public function restore(Request $request, $id)
    {
        $journal = Journal::where('user_id', $request->user()->id)
            ->onlyTrashed()
            ->where('id', $id)
            ->first();

        if (!$journal) {
            return response()->json(['message' => $this->msg('archived_not_found')], 404);
        }

        $journal->restore();

        return response()->json([
            'message' => $this->msg('restore_success'),
            'data' => $journal,
        ]);
    }

    // Statistik jurnal
    public function stats(Request $request)
    {
        $user = $request->user();
        $journals = Journal::where('user_id', $user->id)->get();
        $totalEntries = $journals->count();
        $activeDays = $journals->groupBy(fn($item) => $item->created_at->format('Y-m-d'))->count();

        $weeklyData = Journal::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->get()
            ->groupBy(fn($item) => $item->created_at->format('D'))
            ->map(fn($dayGroup) => $dayGroup->count());

        return response()->json([
            'message' => $this->msg('stats_success'),
            'stats' => [
                'total_entries' => $totalEntries,
                'active_days' => $activeDays,
                'weekly' => $weeklyData,
            ],
        ]);
    }
}
