<?php

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Vormkracht10\Seo\Checks\Configuration\NoIndexCheck;

it('can perform the noindex check with robots tag', function () {
    $check = new NoIndexCheck();
    $crawler = new Crawler();

    Http::fake([
        'vormkracht10.nl' => Http::response('', 200, ['X-Robots-Tag' => 'noindex']),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the noindex check with robots metatag', function () {
    $check = new NoIndexCheck();
    $crawler = new Crawler();

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head><meta name="robots" content="noindex"></head></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the noindex check with googlebot metatag', function () {
    $check = new NoIndexCheck();
    $crawler = new Crawler();

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head><meta name="googlebot" content="noindex"></head></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});
