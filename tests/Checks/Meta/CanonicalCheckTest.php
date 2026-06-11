<?php

use Backstage\Seo\Checks\Meta\CanonicalCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the canonical check on a page with a working canonical url', function () {
    $check = new CanonicalCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><link rel="canonical" href="https://backstagephp.com/page"></head><body></body></html>', 200),
        'https://backstagephp.com/page' => Http::response('', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the canonical check on a page without a canonical url', function () {
    $check = new CanonicalCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the canonical check on a page with a broken canonical url', function () {
    $check = new CanonicalCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><link rel="canonical" href="https://backstagephp.com/broken"></head><body></body></html>', 200),
        'https://backstagephp.com/broken' => Http::response('', 404),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});
