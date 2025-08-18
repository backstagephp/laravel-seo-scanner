<?php

use Backstage\Seo\Checks\Performance\HtmlSizeCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the HTML size check on HTML that is smaller than 100 KB', function () {
    $check = new HtmlSizeCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head></head><body>abcdefghij</body></html>', 200),
    ]);

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the HTML size check on HTML that is larger than 100 KB', function () {
    $check = new HtmlSizeCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head></head><body>'.str_repeat('abcdefghij', 10000).'</body></html>', 200),
    ]);

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});
