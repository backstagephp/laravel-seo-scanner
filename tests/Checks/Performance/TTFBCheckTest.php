<?php

use Backstage\Seo\Checks\Performance\TtfbCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform the ttfb check', function () {
    $this->markTestSkipped('We can\'t fully rely on this test as we can\'t manually set the ttfb value.');

    $check = new TtfbCheck;

    Http::fake([
        'backstagephp.com/robots.txt' => Http::response('<html></html>', 200),
    ]);

    /**
     * Because we can't manually set the ttfb value, we'll just check if the check
     * returns true or false. If it returns false, we'll check if the actual value
     * is higher than the expected value. If it is, we'll check if the check returns
     * false. If it doesn't, we'll check if the check returns true.
     */
    if ($check->check(Http::get('backstagephp.com'), new Crawler) && $check->actualValue > $check->expectedValue) {
        $this->assertFalse($check->check(Http::get('backstagephp.com'), new Crawler));
    } else {
        $this->assertTrue($check->check(Http::get('backstagephp.com'), new Crawler));
    }
});
