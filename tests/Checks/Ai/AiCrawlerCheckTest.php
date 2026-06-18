<?php

use Backstage\Seo\Checks\Ai\AiCrawlerCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('passes when no robots.txt is present', function () {
    $check = new AiCrawlerCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/robots.txt' => Http::response('Not found', 404),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeTrue();
});

it('passes when AI crawlers are not blocked', function () {
    $check = new AiCrawlerCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/robots.txt' => Http::response("User-agent: *\nDisallow: /admin", 200),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeTrue();
});

it('fails when a specific AI crawler is fully disallowed', function () {
    $check = new AiCrawlerCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/robots.txt' => Http::response("User-agent: GPTBot\nDisallow: /", 200),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeFalse();
    expect($check->actualValue)->toContain('GPTBot');
});

it('fails when a wildcard rule blocks the whole site', function () {
    $check = new AiCrawlerCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/robots.txt' => Http::response("User-agent: *\nDisallow: /", 200),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeFalse();
});
