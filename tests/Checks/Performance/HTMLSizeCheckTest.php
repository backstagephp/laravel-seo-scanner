<?php

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Backstage\Seo\Checks\Performance\HtmlSizeCheck;

it('can perform the HTML size check on HTML that is smaller than 100 KB', function () {
    $check = new HtmlSizeCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body>abcdefghij</body></html>', 200),
    ]);

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the HTML size check on HTML that is larger than 100 KB', function () {
    $check = new HtmlSizeCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body>'.str_repeat('abcdefghij', 10000).'</body></html>', 200),
    ]);

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});
