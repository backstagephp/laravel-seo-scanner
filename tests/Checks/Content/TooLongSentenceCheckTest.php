<?php

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Backstage\Seo\Checks\Content\TooLongSentenceCheck;

it('can perform the too long sentence check on page with too long sentence', function () {
    $check = new TooLongSentenceCheck;
    $crawler = new Crawler;

    $body = 'One two three four five six seven eight nine ten eleven twelve thirteen fourteen fifteen sixteen seventeen eighteen nineteen twenty twenty-one.';
    $body .= $body; // Needed because we need a ratio of 20% or more.

    Http::fake([
        'vormkracht10.nl' => Http::response(
            '<html>
                <head>
                    <title>Test</title>
                </head>
                <body>
                    <p>'.$body.'</p>
                </body>',
            200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the too long sentence check on page with no too long sentence', function () {
    $check = new TooLongSentenceCheck;
    $crawler = new Crawler;

    $body = 'One two three four five six seven eight nine ten eleven twelve thirteen fourteen fifteen sixteen seventeen eighteen';

    Http::fake([
        'vormkracht10.nl' => Http::response(
            '<html>
                <head>
                    <title>Test</title>
                </head>
                <body>
                    <p>'.$body.'</p>
                </body>',
            200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $check->check(Http::get('vormkracht10.nl'), $crawler);

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the too long sentence check on page with no body', function () {
    $check = new TooLongSentenceCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response(
            '<html>
                <head>
                    <title>Test</title>
                </head>
                <body></body>',
            200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $check->check(Http::get('vormkracht10.nl'), $crawler);

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});
