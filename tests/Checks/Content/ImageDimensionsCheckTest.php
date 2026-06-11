<?php

use Backstage\Seo\Checks\Content\ImageDimensionsCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the image dimensions check on a page with width and height attributes', function () {
    $check = new ImageDimensionsCheck;
    $crawler = new Crawler;

    $html = '<html><body><img src="https://backstagephp.com/image.jpg" width="640" height="480"></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the image dimensions check on a page with images missing dimensions', function () {
    $check = new ImageDimensionsCheck;
    $crawler = new Crawler;

    $html = '<html><body><img src="https://backstagephp.com/image.jpg"></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the image dimensions check on a page without images', function () {
    $check = new ImageDimensionsCheck;
    $crawler = new Crawler;

    $html = '<html><body><p>No images</p></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});
