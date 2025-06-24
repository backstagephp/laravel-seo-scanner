<?php

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Backstage\Seo\Checks\Meta\TitleLengthCheck;

it('can perform the title length check on a page with a too long title', function () {
    $check = new TitleLengthCheck;
    $crawler = new Crawler;

    $title = str_repeat('a', 61);

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head><title>'.$title.'</title></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the title length check on a page with a short title', function () {
    $check = new TitleLengthCheck;
    $crawler = new Crawler;

    $title = str_repeat('a', 60);

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head><title>'.$title.'</title></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the title length check on a page without a title', function () {
    $check = new TitleLengthCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});
