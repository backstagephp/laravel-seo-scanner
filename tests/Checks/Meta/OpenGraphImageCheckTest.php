<?php

use Backstage\Seo\Checks\Meta\OpenGraphImageCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform open graph image check on a page with a broken open graph image', function () {
    $check = new OpenGraphImageCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><meta property="og:image" content="https://backstagephp.com/images/og-image.png"></head><body></body></html>', 200),
        'https://backstagephp.com/images/og-image.png' => Http::response('', 404),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform open graph image check on a page without an open graph image', function () {
    $check = new OpenGraphImageCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertFalse($check->check(Http::get('backstagephp.com'), $crawler));
});

it('can perform open graph image check on a page with a working open graph image', function () {
    $this->withoutExceptionHandling();
    $check = new OpenGraphImageCheck;
    $crawler = new Crawler;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head><meta property="og:image" content="https://picsum.photos/200/300"></head><body></body></html>', 200),
        'https://picsum.photos/200/300' => Http::response('', 200),
    ]);

    $crawler->addHtmlContent(Http::get('backstagephp.com')->body());

    $check->url = 'backstagephp.com';

    $this->assertTrue($check->check(Http::get('backstagephp.com'), $crawler));
});
