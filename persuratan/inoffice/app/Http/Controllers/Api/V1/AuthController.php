<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     * Login dengan username/email + password, return Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login'    => 'required|string',   // username atau email
            'password' => 'required|string',
        ], [
            'login.required'    => 'Username atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $login = $request->login;
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (!Auth::attempt([$field => $login, 'password' => $request->password])) {
            return response()->json([
                'message' => 'Username/email atau password salah.',
            ], 401);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return response()->json([
                'message' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ], 403);
        }

        // Update last login
        $user->update(['last_login' => now()]);

        // Buat token Sanctum (expire sesuai config: 10080 menit / 7 hari)
        $token = $user->createToken('mobile-app')->plainTextToken;

        $user->load(['roles', 'unitKerja']);

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => new UserResource($user),
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     * Revoke current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * GET /api/v1/auth/me
     * Ambil data user yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'unitKerja']);

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }
}
