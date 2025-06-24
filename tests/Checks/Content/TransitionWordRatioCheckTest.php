<?php

use Backstage\Seo\Checks\Content\TransitionWordRatioCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the transition word ratio check where sentence matches criteria', function () {
    $check = new TransitionWordRatioCheck;
    $crawler = new Crawler;

    $body = 'This is the first sentence. This is the second sentence, which contains a transition word. This is the third sentence. This is the fourth sentence, which also contains a transition word. This is the fifth sentence.';

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

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the transition word ratio check where sentence does not match criteria', function () {
    $check = new TransitionWordRatioCheck;
    $crawler = new Crawler;

    $body = 'Lorem ipsum. Dolor sit amet. This is the next sentence. Fourth sentence. Fifth sentence.';

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

it('can perform the transition word ratio check on page without content', function () {
    $check = new TransitionWordRatioCheck;
    $crawler = new Crawler;

    $body = '';

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
