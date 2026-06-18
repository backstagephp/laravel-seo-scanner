<?php

use Backstage\Seo\Checks\Content\MultipleHeadingCheck;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Stub every request so the scan runs against a known page instead of
    // hitting the live site, which made these tests flaky in CI.
    Http::fake([
        '*' => Http::response(
            '<html><head><title>Test page</title></head><body><h1>Test</h1><p>Content</p></body></html>',
            200
        ),
    ]);
});

it('can run the SEO check for a single URL', function () {
    $this->artisan('seo:scan-url', ['url' => 'https://backstagephp.com'])
        ->assertExitCode(0);
});

it('can run the SEO check for routes', function () {
    config(['seo.database.save' => false]);
    config(['seo.routes' => [
        'https://backstagephp.com',
    ]]);
    config(['seo.checks' => [
        MultipleHeadingCheck::class,
    ]]);

    $this->artisan('seo:scan')
        ->assertExitCode(0);
});

it('can only run configured checks', function () {
    config(['seo.database.save' => false]);
    config(['seo.check_routes' => false]);
    config(['seo.checks' => [
        MultipleHeadingCheck::class,
    ]]);

    $this->artisan('seo:scan-url', ['url' => 'https://backstagephp.com'])
        ->expectsOutputToContain('1 out of '.getCheckCount().' checks.')
        ->assertExitCode(0);
});
