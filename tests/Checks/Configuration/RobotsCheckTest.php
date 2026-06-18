<?php

use Backstage\Seo\Checks\Configuration\RobotsCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('passes when Googlebot is allowed to index the page', function () {
    $check = new RobotsCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/robots.txt' => Http::response("User-agent: Googlebot\nDisallow: /admin", 200),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeTrue();
});

it('fails when Googlebot is fully disallowed', function () {
    $check = new RobotsCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/robots.txt' => Http::response("User-agent: Googlebot\nDisallow: /", 200),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeFalse();
});

it('passes when no robots.txt is present', function () {
    $check = new RobotsCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/robots.txt' => Http::response('Not found', 404),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeTrue();
});
