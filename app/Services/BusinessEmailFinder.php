<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BusinessEmailFinder
{
    public function find(?string $website): ?string
    {
        if (! $website) {
            return null;
        }

        return $this->findMany([$website])[$website] ?? null;
    }

    /** @param array<int, string|null> $websites @return array<string, string|null> */
    public function findMany(array $websites): array
    {
        $results = [];
        $pending = [];

        foreach (array_values(array_unique(array_filter($websites))) as $website) {
            if (! $this->isPublicWebsite($website)) {
                $results[$website] = null;

                continue;
            }

            $cacheKey = $this->cacheKey($website);
            $cachedEmail = Cache::get($cacheKey);

            if (is_string($cachedEmail)) {
                $results[$website] = $cachedEmail !== '' ? $cachedEmail : null;

                continue;
            }

            $pending[] = $website;
        }

        if ($pending === []) {
            return $results;
        }

        $responses = Http::pool(function (Pool $pool) use ($pending): array {
            return collect($pending)->map(
                fn (string $website, int $index) => $pool->as((string) $index)
                    ->connectTimeout(1)
                    ->timeout(3)
                    ->withoutRedirecting()
                    ->withHeaders(['User-Agent' => 'Tech4Projects Business Contact Finder/1.0'])
                    ->get($website),
            )->all();
        }, concurrency: (int) config('gmb_extractor.email_request_concurrency', 5));

        foreach ($pending as $index => $website) {
            $response = $responses[(string) $index] ?? null;
            $email = $response instanceof Response ? $this->emailFromResponse($response) : null;
            $results[$website] = $email;

            Cache::put(
                $this->cacheKey($website),
                $email ?? '',
                now()->addDays((int) config('gmb_extractor.email_cache_days', 7)),
            );
        }

        return $results;
    }

    private function emailFromResponse(Response $response): ?string
    {
        if (! $response->successful() || ! str_contains((string) $response->header('Content-Type'), 'text/html')) {
            return null;
        }

        return $this->firstPublicEmail(mb_substr($response->body(), 0, 750_000));
    }

    private function isPublicWebsite(string $website): bool
    {
        $parts = parse_url($website);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || $host === 'localhost') {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return (bool) filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        $addresses = gethostbynamel($host);

        return $addresses !== false && collect($addresses)->every(
            fn (string $address): bool => (bool) filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE),
        );
    }

    private function firstPublicEmail(string $html): ?string
    {
        preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu', html_entity_decode(strip_tags($html)), $matches);

        foreach (array_unique($matches[0] ?? []) as $email) {
            $email = mb_strtolower(trim($email));
            if (filter_var($email, FILTER_VALIDATE_EMAIL) && ! preg_match('/\.(png|jpe?g|gif|svg|webp)$/i', $email)) {
                return $email;
            }
        }

        return null;
    }

    private function cacheKey(string $website): string
    {
        return 'gmb-extractor:email:'.hash('sha256', $website);
    }
}
