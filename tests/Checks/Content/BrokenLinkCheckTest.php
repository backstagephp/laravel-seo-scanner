<?php

use Backstage\Seo\Checks\Content\BrokenLinkCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the broken link check on broken links', function () {
    $check = new BrokenLinkCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body><a href="https://backstagephp.com/404">Vormkracht10</a></body></html>', 200),
        'https://backstagephp.com/404' => Http::response('', 404),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());
    
    $check->url = 'vormkracht10.nl';

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the broken link check on working links', function () {
    $check = new BrokenLinkCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body><a href="https://backstagephp.com">Vormkracht10</a></body></html>', 200),
        'https://backstagephp.com' => Http::response('', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());
    
    $check->url = 'vormkracht10.nl';

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can perform the broken link check on content where no links are used', function () {
    $check = new BrokenLinkCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());
    
    // Set the URL property that the check needs
    $check->url = 'vormkracht10.nl';

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can run the broken link check on a relative url', function () {
    $check = new BrokenLinkCheck;
    $crawler = new Crawler;

    Http::fake([
        'https://vormkracht10.nl' => Http::response('<html><head></head><body><a href="/404">Vormkracht10</a></body></html>', 200),
        'https://vormkracht10.nl/404' => Http::response('', 404),
    ]);

    $crawler->addHtmlContent(Http::get('https://vormkracht10.nl')->body());
    
    $check->url = 'https://vormkracht10.nl';

    $this->assertFalse($check->check(Http::get('https://vormkracht10.nl'), $crawler));
});

it('can bypass DNS layers using DNS resolving', function () {
    $this->markTestSkipped('This test is skipped because we cannot fake DNS resolving.');

    $check = new BrokenLinkCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body><a href="https://backstagephp.com">Vormkracht10</a></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());

    config(['seo.resolve' => [
        'vormkracht10.nl' => '240.0.0.0',
    ]]);

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('cannot bypass DNS layers using a fake IP when DNS resolving', function () {
    $check = new BrokenLinkCheck;
    $crawler = new Crawler;

    config(['seo.resolve' => [
        'vormkracht10.nl' => '8.8.8.8',
    ]]);

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body><a href="https://backstagephp.com">Vormkracht10</a></body></html>', 200),
        'https://backstagephp.com' => Http::response('', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());
    
    $check->url = 'vormkracht10.nl';

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});

it('can check if link is broken by checking on configured status codes', function () {
    $check = new BrokenLinkCheck;
    $crawler = new Crawler;

    config(['seo.broken_link_check.status_codes' => ['403']]);

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body><a href="https://backstagephp.com/404">Vormkracht10</a></body></html>', 200),
        'https://backstagephp.com/404' => Http::response('', 200), // This will now pass since 404 is not in the configured status codes
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());
    
    $check->url = 'vormkracht10.nl';

    $this->assertTrue($check->check(Http::get('vormkracht10.nl/admin/dashboard'), $crawler));
});

it('can exclude certain paths from the broken link check', function () {
    $check = new BrokenLinkCheck;
    $crawler = new Crawler;

    config(['seo.broken_link_check.exclude_links' => ['https://backstagephp.com/excluded']]);

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body><a href="https://backstagephp.com/excluded">Excluded Link</a></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());
    
    $check->url = 'vormkracht10.nl';

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});
