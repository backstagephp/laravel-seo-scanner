<?php

use Backstage\Seo\Checks\Performance\ResponseCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform response check on a page with a 200 status code', function () {
    $check = new ResponseCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform response check on a page with a 404 status code', function () {
    $check = new ResponseCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head></head><body></body></html>', 404),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});
