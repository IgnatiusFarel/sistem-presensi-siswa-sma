<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait HasLocationResolver
{
    private function getLokasiFromKoordinat($lat, $lng): ?string
    {
        try {
            $response = Http::timeout(5)->get("https://nominatim.openstreetmap.org/reverse", [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'json',
            ]);

            if ($response->successful()) {
                return $response->json('display_name') ?? null;
            }
        } catch (\Throwable $th) {
            \Log::warning("Gagal ambil lokasi dari koordinat: " . $e->getMessage());
        }

        return null;
    }

    public function resolveLocation($lat, $lng): ?string
    {
        return $this->getLokasiFromKoordinat($lat, $lng);
    }
}
