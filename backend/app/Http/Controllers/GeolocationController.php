<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeolocationController extends Controller
{
    public function reverseGeocode(Request $request)
    {
        try {
            $lat = $request->query('lat');
            $lon = $request->query('lon');

            if (!$lat || !$lon) {
                return response()->json(['error' => 'Invalid coordinates'], 400);
            }

            $response = Http::withHeaders([
                'User-Agent' => 'SistemPresensiSiswaSMA/1.0 (ignatius@email.com)'
            ])->get('https://nominatim.openstreetmap.org/reverse', [
                        'format' => 'json',
                        'lat' => $lat,
                        'lon' => $lon,
                    ]);

            if ($response->successful()) {
                return $response->json();
            }

            return response()->json(['error' => 'Failed to fetch location'], $response->status());

        } catch (\Throwable $th) {
            $statusCode = isset($response) ? $response->status() : 500;
            return response()->json(['error' => 'Failed to fetch location'], $statusCode);
        }
    }
}
