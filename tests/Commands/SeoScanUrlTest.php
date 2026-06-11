<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

it('outputs valid json when the json format is used', function () {
    Http::fake([
        '*' => Http::response('<html lang="en"><head><title>Test</title></head><body><h1>Hi</h1></body></html>', 200),
    ]);

    Artisan::call('seo:scan-url', [
        'url' => 'https://backstagephp.com',
        '--format' => 'json',
    ]);

    $decoded = json_decode(Artisan::output(), true);

    expect($decoded)->toBeArray();
    expect($decoded)->toHaveKeys(['url', 'score', 'passed', 'failed', 'checks']);
    expect($decoded['checks'])->toHaveKeys(['passed', 'failed']);
    expect($decoded['url'])->toBe('https://backstagephp.com');
});

it('outputs the console format by default', function () {
    Http::fake([
        '*' => Http::response('<html lang="en"><head><title>Test</title></head><body><h1>Hi</h1></body></html>', 200),
    ]);

    Artisan::call('seo:scan-url', [
        'url' => 'https://backstagephp.com',
    ]);

    $output = Artisan::output();

    expect($output)->toContain('passed');
    expect(json_decode($output))->toBeNull();
});
