<?php

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Backstage\Seo\Checks\Performance\CssSizeCheck;

/**
 * @see In this test, we pass the stylesheet as a response to the check method.
 * This is because the check method will try to fetch the stylesheet, but we don't want to
 * do that in tests. We want to get the stylesheet from the Http::fake() method. Otherwise
 * we don't have access to the stylesheet in the test.
 */
it('can perform the CSS size check on a page with a CSS file larger than 15 KB', function () {
    $check = new CssSizeCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head><link rel="stylesheet" href="https://vormkracht10.nl/style.css"></head><body></body></html>', 200),
    ]);

    Http::fake([
        'vormkracht10.nl/style.css' => Http::response(str_repeat('abcdefghij', 10000), 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl/style.css'), $crawler));
});

it('can perform the CSS size check on a page with a CSS file smaller than 15 KB', function () {
    $check = new CssSizeCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head><link rel="stylesheet" href="https://vormkracht10.nl/style.css"></head><body></body></html>', 200),
    ]);

    Http::fake([
        'vormkracht10.nl/style.css' => Http::response('abcdefghij', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertTrue($check->check(Http::get('vormkracht10.nl/style.css'), $crawler));
});

it('can perform the CSS size check on a page with no CSS files', function () {
    $check = new CssSizeCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});
