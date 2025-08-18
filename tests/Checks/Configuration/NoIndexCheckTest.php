<?php

use Backstage\Seo\Checks\Configuration\NoIndexCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the noindex check with robots tag', function () {
    $check = new NoIndexCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('', 200, ['X-Robots-Tag' => 'noindex']),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the noindex check with robots metatag', function () {
    $check = new NoIndexCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><meta name="robots" content="noindex"></head></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the noindex check with googlebot metatag', function () {
    $check = new NoIndexCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><meta name="googlebot" content="noindex"></head></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});
