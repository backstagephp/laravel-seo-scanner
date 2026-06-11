<?php

use Backstage\Seo\Checks\Content\LazyLoadingCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the lazy loading check on a page where below-the-fold images are lazy loaded', function () {
    $check = new LazyLoadingCheck;
    $crawler = new Crawler;

    $html = '<html><body>'
        .'<img src="https://backstagephp.com/hero.jpg">'
        .'<img src="https://backstagephp.com/a.jpg" loading="lazy">'
        .'<img src="https://backstagephp.com/b.jpg" loading="lazy">'
        .'</body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the lazy loading check on a page with eager below-the-fold images', function () {
    $check = new LazyLoadingCheck;
    $crawler = new Crawler;

    $html = '<html><body>'
        .'<img src="https://backstagephp.com/hero.jpg">'
        .'<img src="https://backstagephp.com/a.jpg">'
        .'<img src="https://backstagephp.com/b.jpg">'
        .'</body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform the lazy loading check on a page with a single image', function () {
    $check = new LazyLoadingCheck;
    $crawler = new Crawler;

    $html = '<html><body><img src="https://backstagephp.com/hero.jpg"></body></html>';

    Http::fake(['backstagephp.com' => Http::response($html, 200)]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});
