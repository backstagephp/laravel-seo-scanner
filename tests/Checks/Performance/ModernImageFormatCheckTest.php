<?php

use Backstage\Seo\Checks\Performance\ModernImageFormatCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the modern image format check on a page using webp', function () {
    $check = new ModernImageFormatCheck;
    $crawler = new Crawler;

    $html = '<html><body><img src="https://backstagephp.com/image.webp"></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the modern image format check on a legacy image inside a picture element', function () {
    $check = new ModernImageFormatCheck;
    $crawler = new Crawler;

    $html = '<html><body><picture>'
        .'<source srcset="https://backstagephp.com/image.avif" type="image/avif">'
        .'<img src="https://backstagephp.com/image.jpg">'
        .'</picture></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the modern image format check on a page using a legacy format', function () {
    $check = new ModernImageFormatCheck;
    $crawler = new Crawler;

    $html = '<html><body><img src="https://backstagephp.com/image.jpg"></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});
