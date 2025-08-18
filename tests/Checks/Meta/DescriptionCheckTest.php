<?php

use Backstage\Seo\Checks\Meta\DescriptionCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the description check on a page with multiple description tags', function () {
    $check = new DescriptionCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><meta name="description" content="Vormkracht10 is a web development agency based in Amsterdam."><meta property="og:description" content="Vormkracht10 is a web development agency based in Amsterdam."><meta name="twitter:description" content="Vormkracht10 is a web development agency based in Amsterdam."></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the description check on a page without any description tags', function () {
    $check = new DescriptionCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});
