<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PlacesController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 3) {
            return response()->json(['suggestions' => []]);
        }

        $response = Http::timeout(8)
            ->withHeaders(['Accept-Language' => 'es', 'User-Agent' => 'Motokadi/1.0 (location-lookup)'])
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $query,
                'format' => 'json',
                'addressdetails' => 1,
                'countrycodes' => 've',
                'limit' => 6,
            ]);

        if (! $response->successful()) {
            return response()->json([
                'suggestions' => [],
                'error' => 'No se pudieron cargar sugerencias. Intenta de nuevo.',
            ], 502);
        }

        $suggestions = collect($response->json() ?: [])
            ->map(fn (array $row) => $this->normalize($row))
            ->filter()
            ->values()
            ->all();

        return response()->json(['suggestions' => $suggestions]);
    }

    public function reverse(Request $request): JsonResponse
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return response()->json(['error' => 'Coordenadas inválidas.'], 422);
        }

        $response = Http::timeout(8)
            ->withHeaders(['Accept-Language' => 'es', 'User-Agent' => 'Motokadi/1.0 (location-lookup)'])
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'json',
                'addressdetails' => 1,
            ]);

        if (! $response->successful()) {
            return response()->json(['error' => 'No se pudo completar la dirección.'], 502);
        }

        $row = $response->json() ?: [];
        $normalized = $this->normalize($row);

        return response()->json($normalized ?: ['error' => 'Sin resultados.']);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{label: string, address: string, city: string, lat: float, lng: float}|null
     */
    private function normalize(array $row): ?array
    {
        if (! isset($row['lat'], $row['lon'])) {
            return null;
        }

        $address = $row['address'] ?? [];
        $city = $address['city']
            ?? $address['town']
            ?? $address['village']
            ?? $address['municipality']
            ?? $address['county']
            ?? $address['state']
            ?? '';

        $label = (string) ($row['display_name'] ?? '');
        if ($label === '') {
            return null;
        }

        return [
            'label' => $label,
            'address' => $label,
            'city' => (string) $city,
            'lat' => (float) $row['lat'],
            'lng' => (float) $row['lon'],
        ];
    }
}
