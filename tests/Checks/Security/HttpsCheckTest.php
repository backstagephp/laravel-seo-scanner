<?php

use Backstage\Seo\Checks\Security\HttpsCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('passes when the page is served over HTTPS', function () {
    $check = new HttpsCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake(['*' => Http::response('<html></html>', 200)]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeTrue();
});

it('fails when the page is served over HTTP', function () {
    $check = new HttpsCheck;
    $check->url = 'http://backstagephp.com';

    Http::fake(['*' => Http::response('<html></html>', 200)]);

    expect($check->check(Http::get('http://backstagephp.com'), new Crawler))->toBeFalse();
    expect($check->actualValue)->toBe('http');
});
