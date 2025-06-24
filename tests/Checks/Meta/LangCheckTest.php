<?php

use Backstage\Seo\Checks\Meta\LangCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the lang check on a page with a lang attribute', function () {
    $check = new LangCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html lang="nl"><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the lang check on a page without a lang attribute', function () {
    $check = new LangCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});
