<?php

use Backstage\Seo\Support\JavascriptRenderer;
use Illuminate\Support\Facades\Http;
use Spatie\Browsershot\Browsershot;

afterEach(function () {
    Mockery::close();
});

it('configures networkidle2 and timeout by default', function () {
    config(['seo.javascript_wait' => [
        'strategy' => 'networkidle2',
        'timeout' => 15,
        'delay' => 3000,
        'fallback_on_timeout' => true,
    ]]);

    $browsershot = Mockery::mock(Browsershot::class);
    $browsershot->shouldReceive('timeout')->once()->with(15)->andReturnSelf();
    $browsershot->shouldReceive('waitUntilNetworkIdle')->once()->with(false)->andReturnSelf();

    $result = (new JavascriptRenderer)->applyWaitStrategy($browsershot);

    expect($result)->toBe($browsershot);
});

it('configures networkidle0 when strategy is networkidle0', function () {
    config(['seo.javascript_wait' => [
        'strategy' => 'networkidle0',
        'timeout' => 20,
        'delay' => 3000,
        'fallback_on_timeout' => true,
    ]]);

    $browsershot = Mockery::mock(Browsershot::class);
    $browsershot->shouldReceive('timeout')->once()->with(20)->andReturnSelf();
    $browsershot->shouldReceive('waitUntilNetworkIdle')->once()->with(true)->andReturnSelf();

    (new JavascriptRenderer)->applyWaitStrategy($browsershot);
});

it('uses a fixed delay and no network wait when strategy is delay', function () {
    config(['seo.javascript_wait' => [
        'strategy' => 'delay',
        'timeout' => 15,
        'delay' => 5000,
        'fallback_on_timeout' => true,
    ]]);

    $browsershot = Mockery::mock(Browsershot::class);
    $browsershot->shouldReceive('timeout')->once()->with(15)->andReturnSelf();
    $browsershot->shouldReceive('setDelay')->once()->with(5000)->andReturnSelf();
    $browsershot->shouldNotReceive('waitUntilNetworkIdle');

    (new JavascriptRenderer)->applyWaitStrategy($browsershot);
});

it('defaults to networkidle2 and a 15 second timeout when javascript_wait is not configured', function () {
    config(['seo.javascript_wait' => null]);

    $browsershot = Mockery::mock(Browsershot::class);
    $browsershot->shouldReceive('timeout')->once()->with(15)->andReturnSelf();
    $browsershot->shouldReceive('waitUntilNetworkIdle')->once()->with(false)->andReturnSelf();

    (new JavascriptRenderer)->applyWaitStrategy($browsershot);
});

it('falls back to an immediate render when the waited render fails', function () {
    config(['seo.javascript_wait.fallback_on_timeout' => true]);

    $renderer = new class extends JavascriptRenderer
    {
        public array $waits = [];

        protected function capture(string $url, bool $wait): string
        {
            $this->waits[] = $wait;

            if ($wait) {
                throw new RuntimeException('Navigation timeout');
            }

            return '<html><head><title>Immediate</title></head></html>';
        }
    };

    Http::fake(['*' => Http::response('RAW BODY', 200)]);
    $raw = Http::get('https://example.com');

    $html = $renderer->render('https://example.com', $raw);

    expect($html)->toBe('<html><head><title>Immediate</title></head></html>');
    expect($renderer->waits)->toBe([true, false]);
});

it('falls back to the raw response body when every render fails', function () {
    config(['seo.javascript_wait.fallback_on_timeout' => true]);

    $renderer = new class extends JavascriptRenderer
    {
        protected function capture(string $url, bool $wait): string
        {
            throw new RuntimeException('render failed');
        }
    };

    Http::fake(['*' => Http::response('RAW SERVER HTML', 200)]);
    $raw = Http::get('https://example.com');

    $html = $renderer->render('https://example.com', $raw);

    expect($html)->toBe('RAW SERVER HTML');
});

it('rethrows when fallback_on_timeout is disabled', function () {
    config(['seo.javascript_wait.fallback_on_timeout' => false]);

    $renderer = new class extends JavascriptRenderer
    {
        protected function capture(string $url, bool $wait): string
        {
            throw new RuntimeException('Navigation timeout');
        }
    };

    Http::fake(['*' => Http::response('RAW', 200)]);
    $raw = Http::get('https://example.com');

    $renderer->render('https://example.com', $raw);
})->throws(RuntimeException::class, 'Navigation timeout');
