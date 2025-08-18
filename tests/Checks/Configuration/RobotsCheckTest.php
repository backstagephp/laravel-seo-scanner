<?php

use Backstage\Seo\Checks\Configuration\RobotsCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the robots check', function () {
    $check = new RobotsCheck;

    Http::fake([
        'backstagephp.com/robots.txt' => Http::response('User-agent: Googlebot
            Disallow: /admin', 200),
    ]);

    $this->assertTrue($check->check(Http::get('backstagephp.com'), new Crawler));
});
