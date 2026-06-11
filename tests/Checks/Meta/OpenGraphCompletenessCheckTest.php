<?php

use Backstage\Seo\Checks\Meta\OpenGraphCompletenessCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the open graph completeness check on a page with all required tags', function () {
    $check = new OpenGraphCompletenessCheck;
    $crawler = new Crawler;

    $html = '<html><head>'
        .'<meta property="og:title" content="Title">'
        .'<meta property="og:description" content="Description">'
        .'<meta property="og:url" content="https://backstagephp.com">'
        .'<meta property="og:type" content="website">'
        .'</head><body></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the open graph completeness check on a page with missing tags', function () {
    $check = new OpenGraphCompletenessCheck;
    $crawler = new Crawler;

    $html = '<html><head><meta property="og:title" content="Title"></head><body></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});
