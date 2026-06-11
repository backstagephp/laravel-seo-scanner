<?php

namespace Backstage\Seo\Support;

use Illuminate\Support\Facades\Http;

/**
 * A thin client for the Google PageSpeed Insights API (Lighthouse).
 *
 * This intentionally has no third-party dependency: it simply calls the
 * public PageSpeed Insights endpoint through Laravel's HTTP client and
 * caches the parsed result per url + strategy.
 */
class PageSpeed
{
    public const ENDPOINT = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

    public static function enabled(): bool
    {
        return ! empty(config('seo.pagespeed.api_key'));
    }

    /**
     * Fetch and parse the PageSpeed Insights metrics for a url.
     *
     * Returns null when the request fails. The result is cached so that
     * multiple checks for the same url only trigger a single API call.
     */
    public static function for(string $url, ?string $strategy = null): ?array
    {
        $strategy = $strategy ?? config('seo.pagespeed.strategy', 'mobile');

        return cache()
            ->driver(config('seo.cache.driver'))
            ->tags('seo')
            ->rememberForever('pagespeed-'.$strategy.'-'.$url, function () use ($url, $strategy) {
                return self::fetch($url, $strategy);
            });
    }

    private static function fetch(string $url, string $strategy): ?array
    {
        $response = Http::timeout((int) config('seo.pagespeed.timeout', 60))
            ->get(self::ENDPOINT, array_filter([
                'url' => $url,
                'strategy' => $strategy,
                'category' => 'performance',
                'key' => config('seo.pagespeed.api_key'),
            ]));

        if (! $response->successful()) {
            return null;
        }

        $score = $response->json('lighthouseResult.categories.performance.score');
        $audits = $response->json('lighthouseResult.audits', []);

        return [
            'score' => $score !== null ? (int) round($score * 100) : null,
            'lcp' => $audits['largest-contentful-paint']['numericValue'] ?? null,
            'cls' => $audits['cumulative-layout-shift']['numericValue'] ?? null,
            'tbt' => $audits['total-blocking-time']['numericValue'] ?? null,
        ];
    }
}
