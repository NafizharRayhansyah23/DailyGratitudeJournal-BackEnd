<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    private $messages = [
        'get_success' => [
            'id' => 'Data profil berhasil diambil.',
            'en' => 'Profile data retrieved successfully.',
        ],
        'update_success' => [
            'id' => 'Profil berhasil diperbarui.',
            'en' => 'Profile updated successfully.',
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

    public function show(Request $request)
    {
        return response()->json([
            'message' => $this->msg('get_success'),
            'data' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id),
                ],
                'birth_date' => 'nullable|date_format:Y-m-d',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => $this->msg('validation_error'),
                'errors' => $e->errors(),
            ], 422);
        }

        $user->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'birth_date' => $validatedData['birth_date'] ?? $user->birth_date,
        ]);

        return response()->json([
            'message' => $this->msg('update_success'),
            'data' => $user,
        ]);
    }
}
