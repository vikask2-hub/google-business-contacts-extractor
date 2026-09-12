<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GooglePlacesService
{
    public function __construct(private BusinessEmailFinder $emailFinder) {}

    /** @return array{results: array<int, array<string, string|null>>, mode: string, notice: string|null} */
    public function preview(string $city, string $category): array
    {
        return [
            'results' => $this->demoResults($city, $category),
            'mode' => 'demo',
            'notice' => 'Preview data is shown until a Google Places API key is connected.',
        ];
    }

    /** @return array{results: array<int, array<string, string|null>>, mode: string, notice: string|null} */
    public function search(string $city, string $category): array
    {
        $apiKey = (string) config('services.google_places.key');

        if ($apiKey === '') {
            return $this->preview($city, $category);
        }

        $cacheKey = 'gmb-extractor:search:v2:'.hash('sha256', $city.'|'.$category);
        $cachedSearch = Cache::get($cacheKey);

        if (is_array($cachedSearch)) {
            return $cachedSearch;
        }

        try {
            $response = Http::connectTimeout(3)
                ->timeout(12)
                ->withOptions(['force_ip_resolve' => 'v4'])
                ->acceptJson()
                ->withHeaders([
                    'X-Goog-Api-Key' => $apiKey,
                    'X-Goog-FieldMask' => 'places.id,places.displayName,places.formattedAddress,places.websiteUri,places.nationalPhoneNumber,places.googleMapsUri',
                ])
                ->post((string) config('services.google_places.endpoint'), [
                    'textQuery' => $this->categoryLabel($category).' in '.$this->cityLabel($city).', India',
                    'languageCode' => 'en',
                    'regionCode' => 'IN',
                    'maxResultCount' => 10,
                ]);

            if (! $response->successful()) {
                $this->logFailure($response);

                return $this->fallback($city, $category);
            }

            $results = collect($response->json('places', []))
                ->map(fn (array $place): array => $this->mapPlace($place, $city))
                ->values()
                ->all();
            $emails = $this->emailFinder->findMany(array_column($results, 'website'));
            $results = collect($results)->map(function (array $business) use ($emails): array {
                $website = $business['website'];
                $business['email'] = is_string($website) ? ($emails[$website] ?? null) : null;

                return $business;
            })->all();

            $search = [
                'results' => $results,
                'mode' => 'live',
                'notice' => $results === [] ? 'Google Places returned no matching businesses. Try another selection.' : null,
            ];

            Cache::put(
                $cacheKey,
                $search,
                now()->addMinutes((int) config('gmb_extractor.search_cache_minutes', 360)),
            );

            return $search;
        } catch (\Throwable $exception) {
            Log::warning('Google Places search could not be completed.', ['exception' => $exception::class]);

            return $this->fallback($city, $category);
        }
    }

    /** @param array<string, mixed> $place @return array<string, string|null> */
    private function mapPlace(array $place, string $city): array
    {
        $website = $this->externalUrl($place['websiteUri'] ?? null);

        return [
            'name' => (string) data_get($place, 'displayName.text', 'Unnamed business'),
            'city' => $this->cityLabel($city),
            'address' => (string) ($place['formattedAddress'] ?? 'Address not listed'),
            'website' => $website,
            'email' => null,
            'phone' => $place['nationalPhoneNumber'] ?? null,
            'maps_url' => $this->externalUrl($place['googleMapsUri'] ?? null),
        ];
    }

    /** @return array{results: array<int, array<string, string|null>>, mode: string, notice: string} */
    private function fallback(string $city, string $category): array
    {
        return [
            'results' => $this->demoResults($city, $category),
            'mode' => 'demo',
            'notice' => 'Live Google Places search is temporarily unavailable, so preview data is shown.',
        ];
    }

    /** @return array<int, array<string, string|null>> */
    private function demoResults(string $city, string $category): array
    {
        $cityLabel = $this->cityLabel($city);
        $categoryLabel = $this->categoryLabel($category);
        $businessNames = ['Northstar', 'BluePeak', 'UrbanCraft', 'BrightPath', 'Cedar & Co.', 'MetroLink', 'Sunrise', 'Vertex'];
        $areas = config("gmb_extractor.cities.{$city}.areas", ['Central Business District']);

        return collect($businessNames)->map(function (string $name, int $index) use ($areas, $categoryLabel, $cityLabel): array {
            $slug = Str::slug($name.' '.$categoryLabel);

            return [
                'name' => $name.' '.$categoryLabel,
                'city' => $cityLabel,
                'address' => $areas[$index % count($areas)].', '.$cityLabel,
                'website' => 'https://example.com/'.$slug,
                'email' => 'hello@'.$slug.'.example',
                'phone' => '+91 90000 '.str_pad((string) (11000 + $index * 137), 5, '0', STR_PAD_LEFT),
                'maps_url' => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($name.' '.$categoryLabel.' '.$cityLabel),
            ];
        })->all();
    }

    private function cityLabel(string $city): string
    {
        return (string) config("gmb_extractor.cities.{$city}.label", Str::headline($city));
    }

    private function categoryLabel(string $category): string
    {
        return (string) config("gmb_extractor.categories.{$category}.label", Str::headline($category));
    }

    private function externalUrl(mixed $url): ?string
    {
        if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true) ? $url : null;
    }

    private function logFailure(Response $response): void
    {
        Log::warning('Google Places search returned an unsuccessful response.', [
            'status' => $response->status(),
            'error' => $response->json('error.status'),
        ]);
    }
}
