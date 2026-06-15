<?php

use Backstage\Seo\Checks\Security\SecurityHeadersCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('passes when all recommended security headers are present', function () {
    $check = new SecurityHeadersCheck;

    Http::fake([
        '*' => Http::response('<html></html>', 200, [
            'Strict-Transport-Security' => 'max-age=31536000',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ]),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeTrue();
});

it('fails when security headers are missing', function () {
    $check = new SecurityHeadersCheck;

    Http::fake([
        '*' => Http::response('<html></html>', 200, [
            'X-Content-Type-Options' => 'nosniff',
        ]),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeFalse();
    expect($check->actualValue)->toContain('Strict-Transport-Security');
    expect($check->actualValue)->not->toContain('X-Content-Type-Options');
});
