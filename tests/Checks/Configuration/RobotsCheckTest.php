<?php

use Backstage\Seo\Checks\Configuration\RobotsCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the robots check', function () {
    $check = new RobotsCheck;

    Http::fake([
        'vormkracht10.nl/robots.txt' => Http::response('User-agent: Googlebot
            Disallow: /admin', 200),
    ]);

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), new Crawler));
});
