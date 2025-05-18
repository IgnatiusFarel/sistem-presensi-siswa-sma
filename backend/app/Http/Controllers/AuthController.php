<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function masuk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Masukkan Email dan Kata Sandi Akun',
                'errors' => $validator->errors()
            ], 422);
        }

         $user = User::where('email', $request->email)->first();

         if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun belum terdaftar'
            ], 404);
        }

         if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi salah'
            ], 401);
        }

         $token = $user->createToken('auth_token')->plainTextToken;

         return response()->json([
            'success' => true,
            'message' => 'Masuk berhasil',
            'data' => [
                'user' => [
                    'user_id' => $user->user_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'token' => $token
            ]
        ]);
    }

   public function keluar(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Keluar berhasil'
        ]);
    }
}
