<?php

use Backstage\Seo\Checks\Meta\HreflangCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the hreflang check on a page with valid hreflang annotations', function () {
    $check = new HreflangCheck;
    $crawler = new Crawler;

    $html = '<html><head>'
        .'<link rel="alternate" hreflang="en" href="https://backstagephp.com/en">'
        .'<link rel="alternate" hreflang="nl-NL" href="https://backstagephp.com/nl">'
        .'<link rel="alternate" hreflang="x-default" href="https://backstagephp.com">'
        .'</head><body></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the hreflang check on a page with invalid hreflang annotations', function () {
    $check = new HreflangCheck;
    $crawler = new Crawler;

    $html = '<html><head>'
        .'<link rel="alternate" hreflang="english" href="https://backstagephp.com/en">'
        .'</head><body></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the hreflang check on a page without hreflang annotations', function () {
    $check = new HreflangCheck;
    $crawler = new Crawler;

    $html = '<html><head></head><body></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});
