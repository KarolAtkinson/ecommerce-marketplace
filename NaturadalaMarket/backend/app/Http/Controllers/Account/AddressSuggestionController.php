<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AddressSuggestionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:3', 'max:120'],
            'country' => ['nullable', 'string', 'size:2', 'alpha'],
        ]);

        $query = trim($data['q']);
        $country = strtolower($data['country'] ?? 'pl');

        $suggestions = Cache::remember(
            'address-suggest:'.sha1($country.'|'.mb_strtolower($query)),
            now()->addDay(),
            fn () => $this->lookup($query, $country)
        );

        return response()->json(['suggestions' => $suggestions ?? []]);
    }

    private function lookup(string $query, string $country): ?array
    {
        try {
            $response = Http::timeout(6)
                ->withHeaders([
                    'User-Agent' => config('app.name', 'Naturadala').' address-lookup',
                    'Accept-Language' => 'pl,en',
                ])
                ->get(config('services.address_lookup.url', 'https://nominatim.openstreetmap.org/search'), [
                    'q' => $query,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                    'limit' => 6,
                    'countrycodes' => $country,
                ]);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return collect($response->json())
            ->map(function (array $item) {
                $address = $item['address'] ?? [];
                $street = $address['road'] ?? $address['pedestrian'] ?? $address['footway'] ?? null;
                $city = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['municipality'] ?? null;

                if (! $street || ! $city) {
                    return null;
                }

                $number = $address['house_number'] ?? null;

                return [
                    'street' => trim($street.' '.$number),
                    'postal_code' => $address['postcode'] ?? '',
                    'city' => $city,
                    'region' => preg_replace('/^województwo\s+/iu', '', $address['state'] ?? ''),
                    'country_code' => strtoupper($address['country_code'] ?? ''),
                ];
            })
            ->filter()
            ->unique(fn (array $s) => mb_strtolower($s['street'].'|'.$s['postal_code'].'|'.$s['city']))
            ->values()
            ->all();
    }
}
