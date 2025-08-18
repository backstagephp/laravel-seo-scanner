<?php

use Backstage\Seo\Checks\Performance\CompressionCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

it('can perform a compression check on a compressed response', function () {
    $check = new CompressionCheck;

    $contentEncodings = ['gzip', 'deflate', 'br', 'compress'];

    foreach ($contentEncodings as $contentEncoding) {
        Http::fake([
            'backstagephp.com' => Http::response('<html><head></head><body></body></html>', 200, [
                'Content-Encoding' => $contentEncoding,
            ]),
        ]);

        $this->assertTrue($check->check(Http::get('backstagephp.com'), new Crawler));
    }
});

it('can perform a compression check on a non-compressed response', function () {
    $check = new CompressionCheck;

    Http::fake([
        'backstagephp.com' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $this->assertFalse($check->check(Http::get('backstagephp.com'), new Crawler));
});
