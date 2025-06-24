<?php

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Backstage\Seo\Checks\Performance\CompressionCheck;

it('can perform a compression check on a compressed response', function () {
    $check = new CompressionCheck;

    $contentEncodings = ['gzip', 'deflate', 'br', 'compress'];

    foreach ($contentEncodings as $contentEncoding) {
        Http::fake([
            'vormkracht10.nl' => Http::response('<html><head></head><body></body></html>', 200, [
                'Content-Encoding' => $contentEncoding,
            ]),
        ]);

        $this->assertTrue($check->check(Http::get('vormkracht10.nl'), new Crawler));
    }
});

it('can perform a compression check on a non-compressed response', function () {
    $check = new CompressionCheck;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $this->assertFalse($check->check(Http::get('vormkracht10.nl'), new Crawler));
});
