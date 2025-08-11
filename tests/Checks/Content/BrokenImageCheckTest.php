<?php

use Backstage\Seo\Checks\Content\BrokenImageCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the broken image check on broken images', function () {
    $check = new BrokenImageCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body><img src="https://backstagephp.com/404"></body></html>', 200),
        'https://backstagephp.com/404' => Http::response('', 404),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $check->url = 'vormkracht10.nl';

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the broken image check on working images', function () {
    $check = new BrokenImageCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body><img src="https://backstagephp.com"></body></html>', 200),
        'https://backstagephp.com' => Http::response('', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $check->url = 'vormkracht10.nl';

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the broken image check on content where no images are used', function () {
    $check = new BrokenImageCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $check->url = 'vormkracht10.nl';

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});
