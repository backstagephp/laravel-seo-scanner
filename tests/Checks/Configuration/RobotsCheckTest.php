<?php

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Backstage\Seo\Checks\Configuration\RobotsCheck;

it('can perform the robots check', function () {
    $check = new RobotsCheck;

    Http::fake([
        'vormkracht10.nl/robots.txt' => Http::response('User-agent: Googlebot
            Disallow: /admin', 200),
    ]);

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), new Crawler));
});
