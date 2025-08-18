<?php

use Backstage\Seo\Checks\Content\KeywordInFirstParagraphCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the keyword in first paragraph check on a page with the keyword in the first paragraph', function () {
    $check = new KeywordInFirstParagraphCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><meta name="keywords" content="vormkracht10, seo, laravel, package"></head><body><p>vormkracht10 is a great company that specializes in SEO and Laravel packages.</p></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the keyword in first paragraph check on a page without the keyword in the first paragraph', function () {
    $check = new KeywordInFirstParagraphCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><meta name="keywords" content="seo, laravel, package"></head><body><p>Lorem ipsum dolor sit amet.</p></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the keyword in first paragraph check on a page without keywords', function () {
    $check = new KeywordInFirstParagraphCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});
