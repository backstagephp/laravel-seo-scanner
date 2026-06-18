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

it('does not hard-cap retries, so rate-limit releases are not failed as MaxAttemptsExceeded', function () {
    // A declared `$tries = 1` would fail every throttled chunk the moment the
    // RateLimited middleware releases it back onto the queue.
    expect((new ReflectionClass(ScanChunk::class))->hasProperty('tries'))->toBeFalse();
});

it('allows time-bound retries while throttling is enabled', function () {
    config(['seo.throttle.enabled' => true]);
    config(['seo.throttle.retry_until_hours' => 12]);

    $retryUntil = (new ScanChunk(scanId: 1))->retryUntil();

    expect($retryUntil)->toBeInstanceOf(DateTimeInterface::class)
        ->and($retryUntil->getTimestamp())->toBeGreaterThan(now()->addHours(11)->getTimestamp())
        ->and($retryUntil->getTimestamp())->toBeLessThanOrEqual(now()->addHours(12)->getTimestamp());
});

it('does not bound retries by time when throttling is disabled', function () {
    config(['seo.throttle.enabled' => false]);

    expect((new ScanChunk(scanId: 1))->retryUntil())->toBeNull();
});

it('lets retry_until_hours be disabled so the worker --tries governs', function () {
    config(['seo.throttle.enabled' => true]);
    config(['seo.throttle.retry_until_hours' => null]);

    expect((new ScanChunk(scanId: 1))->retryUntil())->toBeNull();
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
