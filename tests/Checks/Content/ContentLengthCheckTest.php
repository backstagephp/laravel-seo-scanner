<?php

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Backstage\Seo\Checks\Content\ContentLengthCheck;

it('can perform the content length check on content with a length of 2100 characters', function () {
    $check = new ContentLengthCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response(
            '<html>
                <head>
                    <title>Test</title>
                </head>
                <body>
                    <p>'.str_repeat('a', 2100).'</p>
                </body>',
            200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the content length check on content with less characters', function () {
    $check = new ContentLengthCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response(
            '<html>
                <head>
                    <title>Test</title>
                </head>
                <body>
                    <p>'.str_repeat('a', 100).'</p>
                </body>',
            200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});
