<?php

use Backstage\Seo\Checks\PageSpeed\ClsCheck;
use Backstage\Seo\Checks\PageSpeed\LcpCheck;
use Backstage\Seo\Checks\PageSpeed\PerformanceScoreCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

function fakePageSpeed(float $score, int $lcp, float $cls): void
{
    Http::fake([
        'https://www.googleapis.com/*' => Http::response([
            'lighthouseResult' => [
                'categories' => [
                    'performance' => ['score' => $score],
                ],
                'audits' => [
                    'largest-contentful-paint' => ['numericValue' => $lcp],
                    'cumulative-layout-shift' => ['numericValue' => $cls],
                    'total-blocking-time' => ['numericValue' => 120],
                ],
            ],
        ], 200),
        '*' => Http::response('<html><head></head><body></body></html>', 200),
    ]);
}

beforeEach(function () {
    config()->set('seo.pagespeed.api_key', 'test-key');
    cache()->driver(config('seo.cache.driver'))->tags('seo')->flush();
});

it('passes the performance score check when the score is high enough', function () {
    fakePageSpeed(0.95, 1800, 0.05);

    $check = new PerformanceScoreCheck;
    $check->url = 'https://a.backstagephp.com';

    $this->assertTrue($check->check(Http::get('https://a.backstagephp.com'), new Crawler));
});

it('fails the performance score check when the score is too low', function () {
    fakePageSpeed(0.50, 1800, 0.05);

    $check = new PerformanceScoreCheck;
    $check->url = 'https://b.backstagephp.com';

    $this->assertFalse($check->check(Http::get('https://b.backstagephp.com'), new Crawler));
});

it('fails the performance score check when no api key is configured', function () {
    config()->set('seo.pagespeed.api_key', null);

    Http::fake(['*' => Http::response('<html></html>', 200)]);

    $check = new PerformanceScoreCheck;
    $check->url = 'https://c.backstagephp.com';

    $this->assertFalse($check->check(Http::get('https://c.backstagephp.com'), new Crawler));
});

it('passes the lcp check when the lcp is fast', function () {
    fakePageSpeed(0.95, 1800, 0.05);

    $check = new LcpCheck;
    $check->url = 'https://d.backstagephp.com';

    $this->assertTrue($check->check(Http::get('https://d.backstagephp.com'), new Crawler));
});

it('fails the lcp check when the lcp is slow', function () {
    fakePageSpeed(0.95, 4200, 0.05);

    $check = new LcpCheck;
    $check->url = 'https://e.backstagephp.com';

    $this->assertFalse($check->check(Http::get('https://e.backstagephp.com'), new Crawler));
});

it('passes the cls check when the layout shift is low', function () {
    fakePageSpeed(0.95, 1800, 0.05);

    $check = new ClsCheck;
    $check->url = 'https://f.backstagephp.com';

    $this->assertTrue($check->check(Http::get('https://f.backstagephp.com'), new Crawler));
});

it('fails the cls check when the layout shift is high', function () {
    fakePageSpeed(0.95, 1800, 0.35);

    $check = new ClsCheck;
    $check->url = 'https://g.backstagephp.com';

    $this->assertFalse($check->check(Http::get('https://g.backstagephp.com'), new Crawler));
});
