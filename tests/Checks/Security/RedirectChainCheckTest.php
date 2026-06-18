<?php

use Backstage\Seo\Checks\Security\RedirectChainCheck;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

function redirectStats(int $count): TransferStats
{
    return new TransferStats(new Request('GET', 'https://backstagephp.com'), null, null, null, ['redirect_count' => $count]);
}

it('passes when the page is reached without redirects', function () {
    $check = new RedirectChainCheck;

    Http::fake(['*' => Http::response('<html></html>', 200)]);

    expect($check->check(Http::get('https://backstagephp.com'), new Crawler))->toBeTrue();
});

it('passes when the page is reached with a single redirect', function () {
    $check = new RedirectChainCheck;

    Http::fake(['*' => Http::response('<html></html>', 200)]);

    $response = Http::get('https://backstagephp.com');
    $response->transferStats = redirectStats(1);

    expect($check->check($response, new Crawler))->toBeTrue();
});

it('fails when the page is reached through a chain of redirects', function () {
    $check = new RedirectChainCheck;

    Http::fake(['*' => Http::response('<html></html>', 200)]);

    $response = Http::get('https://backstagephp.com');
    $response->transferStats = redirectStats(3);

    expect($check->check($response, new Crawler))->toBeFalse();
    expect($check->actualValue)->toBe(3);
});
