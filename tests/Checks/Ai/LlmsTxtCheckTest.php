<?php

use Backstage\Seo\Checks\Ai\LlmsTxtCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('passes when the site provides a non-empty llms.txt file', function () {
    $check = new LlmsTxtCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/llms.txt' => Http::response("# Backstage\n\nA Laravel package.", 200),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeTrue();
});

it('fails when the llms.txt file is missing', function () {
    $check = new LlmsTxtCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/llms.txt' => Http::response('Not found', 404),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeFalse();
});

it('fails when the llms.txt file is empty', function () {
    $check = new LlmsTxtCheck;
    $check->url = 'https://backstagephp.com';

    Http::fake([
        'backstagephp.com/llms.txt' => Http::response('   ', 200),
        '*' => Http::response('<html></html>', 200),
    ]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeFalse();
});
