<?php

use Backstage\Seo\Checks\Meta\InvalidHeadElementsCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the invalid head elements check on a page with valid head elements', function () {
    $check = new InvalidHeadElementsCheck;
    $crawler = new Crawler;

    $html = '<html><head><title>Test Title</title><meta name="description" content="Test description"><link rel="stylesheet" href="style.css"></head><body></body></html>';

    Http::fake([
        'vormkracht10.nl' => Http::response($html, 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the invalid head elements check on a page with invalid head elements', function () {
    $check = new InvalidHeadElementsCheck;
    $crawler = new Crawler;

    $html = '<html><head><title>Test Title</title><div>Invalid element</div><meta name="description" content="Test description"></head><body></body></html>';

    Http::fake([
        'vormkracht10.nl' => Http::response($html, 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the invalid head elements check on a page with multiple invalid head elements', function () {
    $check = new InvalidHeadElementsCheck;
    $crawler = new Crawler;

    $html = '<html><head><title>Test Title</title><div>Invalid element 1</div><p>Invalid element 2</p><span>Invalid element 3</span><meta name="description" content="Test description"></head><body></body></html>';

    Http::fake([
        'vormkracht10.nl' => Http::response($html, 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the invalid head elements check on a page with no head elements', function () {
    $check = new InvalidHeadElementsCheck;
    $crawler = new Crawler;

    $html = '<html><head></head><body></body></html>';

    Http::fake([
        'vormkracht10.nl' => Http::response($html, 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the invalid head elements check on a page with all valid head elements', function () {
    $check = new InvalidHeadElementsCheck;
    $crawler = new Crawler;

    $html = '<html><head><title>Test Title</title><base href="https://example.com"><link rel="stylesheet" href="style.css"><meta name="description" content="Test description"><style>body { color: black; }</style><script>console.log("test");</script><noscript>JavaScript disabled</noscript><template><div>Template content</div></template></head><body></body></html>';

    Http::fake([
        'vormkracht10.nl' => Http::response($html, 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the invalid head elements check on a page with mixed case element names', function () {
    $check = new InvalidHeadElementsCheck;
    $crawler = new Crawler;

    $html = '<html><head><TITLE>Test Title</TITLE><DIV>Invalid element</DIV><meta name="description" content="Test description"></head><body></body></html>';

    Http::fake([
        'vormkracht10.nl' => Http::response($html, 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});
