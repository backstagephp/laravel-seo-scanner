<?php

use Backstage\Seo\Checks\Content\KeywordInTitleCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the keyword in title check on a page with the keyword in the title', function () {
    $check = new KeywordInTitleCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head><title>vormkracht10</title><meta name="keywords" content="vormkracht10, seo, laravel, package"></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the keyword in title check on a page without the keyword in the title', function () {
    $check = new KeywordInTitleCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head><title>vormkracht10</title><meta name="keywords" content="seo, laravel, package"></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the keyword in title check on a page without keywords', function () {
    $check = new KeywordInTitleCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});
