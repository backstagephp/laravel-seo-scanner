<?php

use Backstage\Seo\Checks\Meta\CharsetCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the charset check on a page with a meta charset tag', function () {
    $check = new CharsetCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><meta charset="utf-8"></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the charset check on a page with a http-equiv content type', function () {
    $check = new CharsetCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the charset check on a page without a character encoding', function () {
    $check = new CharsetCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><title>No charset</title></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});
