<?php

use Backstage\Seo\Checks\Content\KeywordsCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the keywords check on a page with good keyword usage', function () {
    $check = new KeywordsCheck;
    $crawler = new Crawler;

    $html = '<html><head><title>Laravel SEO Package - Best SEO Tools</title><meta name="keywords" content="laravel, seo, package, tools"></head><body><h1>Laravel SEO Package</h1><p>This Laravel SEO package provides excellent SEO tools for your Laravel application. The package includes various SEO checks and tools to improve your website performance.</p></body></html>';

    Http::fake([
        'backstagephp.com' => Http::response($html, 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the keywords check on a page with insufficient keyword usage', function () {
    $check = new KeywordsCheck;
    $crawler = new Crawler;

    $html = '<html><head><title>Some Random Title</title><meta name="keywords" content="laravel, seo, package, tools"></head><body><h1>Welcome to our site</h1><p>This is some random content that does not contain the keywords from the meta tags.</p></body></html>';

    Http::fake([
        'backstagephp.com' => Http::response($html, 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the keywords check on a page without keywords', function () {
    $check = new KeywordsCheck;
    $crawler = new Crawler;

    $html = '<html><head><title>Some Title</title></head><body><h1>Welcome</h1><p>Some content here.</p></body></html>';

    Http::fake([
        'backstagephp.com' => Http::response($html, 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the keywords check on a page without content', function () {
    $check = new KeywordsCheck;
    $crawler = new Crawler;

    $html = '<html><head><title></title><meta name="keywords" content="laravel, seo"></head><body></body></html>';

    Http::fake([
        'backstagephp.com' => Http::response($html, 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the keywords check on a page with partial keyword usage', function () {
    $check = new KeywordsCheck;
    $crawler = new Crawler;

    $html = '<html><head><title>Laravel Development</title><meta name="keywords" content="laravel, seo, package, tools, development"></head><body><h1>Laravel Development</h1><p>This page is about Laravel development and web programming.</p></body></html>';

    Http::fake([
        'backstagephp.com' => Http::response($html, 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the keywords check on a page with mixed case keywords', function () {
    $check = new KeywordsCheck;
    $crawler = new Crawler;

    $html = '<html><head><title>LARAVEL SEO PACKAGE</title><meta name="keywords" content="Laravel, SEO, Package, Tools"></head><body><h1>Laravel SEO Package</h1><p>This Laravel SEO package provides excellent tools for your application.</p></body></html>';

    Http::fake([
        'backstagephp.com' => Http::response($html, 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the keywords check on a page with custom model keywords attribute', function () {
    // Mock the config to use a custom attribute name
    config(['seo.keywords_check.model_keywords_attribute' => 'seo_keywords']);

    $model = new class {
        public $seo_keywords = 'laravel, seo, package';
        public $url = 'https://backstagephp.com';
    };

    $html = '
        <html>
            <head>
                <title>Laravel SEO Package - Best SEO Tool</title>
            </head>
            <body>
                <p>This is a great Laravel SEO package that helps with search engine optimization.</p>
            </body>
        </html>';

    $response = Http::fake([
        'https://backstagephp.com' => Http::response($html, 200),
    ])->get('https://backstagephp.com');

    $crawler = new Crawler($response->body());

    $check = new KeywordsCheck();
    $check->model = $model;

    $result = $check->validateContent($crawler);

    $this->assertTrue($result);
    $this->assertEquals(['laravel', 'seo', 'package'], $check->expectedValue);
});
