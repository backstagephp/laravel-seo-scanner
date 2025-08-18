<?php

use Backstage\Seo\Checks\Meta\TitleLengthCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the title length check on a page with a too long title', function () {
    $check = new TitleLengthCheck;
    $crawler = new Crawler;

    $title = str_repeat('a', 61);

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><title>'.$title.'</title></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the title length check on a page with a short title', function () {
    $check = new TitleLengthCheck;
    $crawler = new Crawler;

    $title = str_repeat('a', 60);

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><title>'.$title.'</title></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the title length check on a page without a title', function () {
    $check = new TitleLengthCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});
