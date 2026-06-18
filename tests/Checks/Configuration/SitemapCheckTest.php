<?php

use Backstage\Seo\Checks\Configuration\SitemapCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('passes when a valid sitemap.xml is present', function () {
    $check = new SitemapCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/robots.txt' => Http::response('User-agent: *', 200),
        'backstagephp.com/sitemap.xml' => Http::response('<?xml version="1.0"?><urlset><url><loc>https://backstagephp.com</loc></url></urlset>', 200),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeTrue();
});

it('passes when the sitemap is a sitemap index', function () {
    $check = new SitemapCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/robots.txt' => Http::response('User-agent: *', 200),
        'backstagephp.com/sitemap.xml' => Http::response('<?xml version="1.0"?><sitemapindex><sitemap><loc>https://backstagephp.com/sitemap-1.xml</loc></sitemap></sitemapindex>', 200),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeTrue();
});

it('uses the sitemap url declared in robots.txt', function () {
    $check = new SitemapCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/robots.txt' => Http::response("User-agent: *\nSitemap: https://backstagephp.com/custom-sitemap.xml", 200),
        'backstagephp.com/custom-sitemap.xml' => Http::response('<?xml version="1.0"?><urlset></urlset>', 200),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeTrue();
    expect($check->actualValue)->toBe('https://backstagephp.com/custom-sitemap.xml');
});

it('fails when no sitemap is present', function () {
    $check = new SitemapCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/robots.txt' => Http::response('User-agent: *', 200),
        'backstagephp.com/sitemap.xml' => Http::response('Not found', 404),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeFalse();
});

it('fails when the sitemap is not valid XML', function () {
    $check = new SitemapCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/robots.txt' => Http::response('User-agent: *', 200),
        'backstagephp.com/sitemap.xml' => Http::response('<html><body>Not a sitemap</body></html>', 200),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeFalse();
});
