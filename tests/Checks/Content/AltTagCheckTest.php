<?php

use Backstage\Seo\Checks\Content\AltTagCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the alt tag check with alt', function () {
    $check = new AltTagCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head></head><body><img src="https://backstagephp.com/images/logo.png" width="5" height="5" alt="Vormkracht10 logo"></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the alt tag check without alt', function () {
    $check = new AltTagCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head></head><body><img src="https://backstagephp.com/images/logo.png" width="5" height="5"></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the alt tag check with empty alt', function () {
    $check = new AltTagCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head></head><body><img src="https://backstagephp.com/images/logo.png" width="5" height="5" alt=""></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});
