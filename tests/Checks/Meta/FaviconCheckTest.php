<?php

use Backstage\Seo\Checks\Meta\FaviconCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the favicon check on a page with a working favicon', function () {
    $check = new FaviconCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><link rel="icon" href="https://backstagephp.com/favicon.ico"></head><body></body></html>', 200),
        'https://backstagephp.com/favicon.ico' => Http::response('', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the favicon check on a page without a favicon', function () {
    $check = new FaviconCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the favicon check on a page with a broken favicon', function () {
    $check = new FaviconCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><link rel="shortcut icon" href="https://backstagephp.com/missing.ico"></head><body></body></html>', 200),
        'https://backstagephp.com/missing.ico' => Http::response('', 404),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});
