<?php

use Backstage\Seo\Jobs\ScanChunk;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\RateLimiter;

it('rate limits chunk jobs when throttling is enabled', function () {
    config(['seo.throttle.enabled' => true]);
    config(['seo.throttle.requests_per_minute' => 60]);

    $middleware = (new ScanChunk(scanId: 1))->middleware();

    expect($middleware)->toHaveCount(1)
        ->and($middleware[0])->toBeInstanceOf(RateLimited::class);
});

it('does not rate limit chunk jobs when throttling is disabled', function () {
    config(['seo.throttle.enabled' => false]);

    expect((new ScanChunk(scanId: 1))->middleware())->toBe([]);
});

it('registers the seo-scan limiter derived from requests per minute and chunk size', function () {
    config(['seo.throttle.enabled' => true]);
    config(['seo.throttle.requests_per_minute' => 120]);
    config(['seo.chunk_size' => 10]);

    $limiter = RateLimiter::limiter('seo-scan');

    expect($limiter)->not->toBeNull();

    $limit = $limiter(new ScanChunk(scanId: 1));

    // 120 requests/min over chunks of 10 pages => 12 jobs/min.
    expect($limit)->toBeInstanceOf(Limit::class)
        ->and($limit->maxAttempts)->toBe(12);
});
