<?php

use Backstage\Seo\Checks\Configuration\NoFollowCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the nofollow check with robots tag', function () {
    $check = new NoFollowCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('', 200, ['X-Robots-Tag' => 'nofollow']),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the nofollow check with robots metatag', function () {
    $check = new NoFollowCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><meta name="robots" content="nofollow"></head></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the nofollow check with googlebot metatag', function () {
    $check = new NoFollowCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><meta name="googlebot" content="nofollow"></head></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});
