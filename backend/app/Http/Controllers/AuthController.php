<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function masuk(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Masukkan Email dan Kata Sandi Akun!',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun belum terdaftar!'
                ], 404);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kata sandi salah!'
                ], 401);
            }

            if (!in_array($user->role, [User::ROLE_SUPERADMIN, User::ROLE_SISWA])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses akun untuk login!'
                ], 403);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Masuk Berhasil!',
                'data' => [
                    'user' => [
                        'user_id' => $user->user_id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                    'token' => $token
                ]
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Login error: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Masuk Gagal!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function keluar(Request $request)
    {
        try {
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $token->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Keluar Berhasil!'
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Logout error: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Keluar Gagal!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
