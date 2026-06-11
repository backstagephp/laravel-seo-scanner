<?php

use Backstage\Seo\Checks\Content\HeadingStructureCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the heading structure check on a page with a logical structure', function () {
    $check = new HeadingStructureCheck;
    $crawler = new Crawler;

    $html = '<html><body><h1>Title</h1><h2>Section</h2><h3>Subsection</h3><h2>Section</h2></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the heading structure check on a page that skips a heading level', function () {
    $check = new HeadingStructureCheck;
    $crawler = new Crawler;

    $html = '<html><body><h1>Title</h1><h3>Skipped h2</h3></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the heading structure check on a page without headings', function () {
    $check = new HeadingStructureCheck;
    $crawler = new Crawler;

    $html = '<html><body><p>No headings here</p></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});
