<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    public function generate(Request $request)
    {
        try {
            $prompt = $request->input('prompt');

            if (!$prompt) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Prompt tidak boleh kosong!'
                ], 400);
            }

            $apiKey = env('GEMINI_API_KEY');

            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ]
                    ]);

            if ($response->successful()) {
                $data = $response->json();
                $output = $data['candidates'][0]['content']['parts'][0]['text'] ?? '(Tidak ada hasil)';
                return response()->json([
                    'status' => 'success',
                    'output' => $output,
                    'message' => 'Generate content berhasil dihasilkan!'
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses permintaan ke Gemini API!',
                'debug' => $response->json()
            ], $response->status());

        } catch (\Throwable $th) {
            Log::error('AI Generate Error: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Generate content gagal dihasilkan!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
