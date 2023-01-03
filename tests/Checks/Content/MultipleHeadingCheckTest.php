<?php

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Vormkracht10\Seo\Checks\Content\MultipleHeadingCheck;

it('can perform the multiple heading check test on no headings', function () {
    $check = new MultipleHeadingCheck();
    $crawler = new Crawler();

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the multiple heading check test on one heading', function () {
    $check = new MultipleHeadingCheck();
    $crawler = new Crawler();

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body><h1>Heading</h1></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the multiple heading check test on multiple headings', function () {
    $check = new MultipleHeadingCheck();
    $crawler = new Crawler();

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body><h1>Heading</h1><h1>Heading</h1></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});
