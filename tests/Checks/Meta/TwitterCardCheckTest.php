<?php

use Backstage\Seo\Checks\Meta\TwitterCardCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the twitter card check on a page with a twitter card', function () {
    $check = new TwitterCardCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><meta name="twitter:card" content="summary_large_image"></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the twitter card check on a page without a twitter card', function () {
    $check = new TwitterCardCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><meta name="description" content="No card"></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});
