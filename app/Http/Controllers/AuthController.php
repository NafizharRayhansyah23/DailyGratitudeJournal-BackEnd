<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    private $messages = [
        'register_success' => [
            'id' => 'Pendaftaran berhasil.',
            'en' => 'Registration successful.',
        ],
        'login_success' => [
            'id' => 'Login berhasil.',
            'en' => 'Login successful.',
        ],
        'logout_success' => [
            'id' => 'Logout berhasil.',
            'en' => 'Logout successful.',
        ],
        'invalid_credentials' => [
            'id' => 'Email atau kata sandi salah.',
            'en' => 'Invalid email or password.',
        ],
        'token_expired' => [
            'id' => 'Token sudah kedaluwarsa.',
            'en' => 'Your token has expired.',
        ],
        'token_active' => [
            'id' => 'Token masih aktif.',
            'en' => 'Your token is still active.',
        ],
    ];

    private function msg($key)
    {
        $lang = request()->query('lang', 'id'); 
        return $this->messages[$key][$lang] ?? $this->messages[$key]['id'];
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'birth_date' => $validatedData['birth_date'] ?? null,
            'password' => Hash::make($validatedData['password']),
        ]);

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;
        $this->setTokenExpiry($user, 2);

        return response()->json([
            'message' => $this->msg('register_success'),
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => $this->msg('invalid_credentials'),
            ], 401);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;
        $this->setTokenExpiry($user, 24);

        return response()->json([
            'message' => $this->msg('login_success'),
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => $this->msg('logout_success'),
        ]);
    }

    public function checkToken(Request $request)
    {
        $token = $this->getCurrentToken($request);

        if (!$token || Carbon::now()->greaterThan($token->expires_at)) {
            if ($token) $token->delete();
            return response()->json([
                'message' => $this->msg('token_expired'),
            ], 401);
        }

        return response()->json([
            'message' => $this->msg('token_active'),
        ]);
    }

    private function setTokenExpiry(User $user, int $hours)
    {
        $lastToken = $user->tokens()->latest()->first();
        if ($lastToken) {
            $lastToken->update([
                'expires_at' => Carbon::now()->addHours($hours),
            ]);
        }
    }

    private function getCurrentToken(Request $request)
    {
        $tokenString = $request->bearerToken();
        if (!$tokenString) return null;

        $hashed = hash('sha256', $tokenString);
        return PersonalAccessToken::where('token', $hashed)->first();
    }
}
