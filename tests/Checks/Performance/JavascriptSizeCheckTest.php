<?php

use Backstage\Seo\Checks\Performance\JavascriptSizeCheck;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @see In this test, we pass the javascript file as a response to the check method.
 * This is because the check method will try to fetch the javascript file, but we don't want to
 * do that in tests. We want to get the javascript file from the Http::fake() method. Otherwise
 * we don't have access to the javascript file in the test.
 */
it('can perform the Javascript size check on a page with a Javascript file larger than 1 MB', function () {
    $check = new JavascriptSizeCheck;
    $crawler = new Crawler;

    // Create a response body larger than 1MB (1,000,001 bytes to be exact)
    $largeBody = str_repeat('a', 1000001);

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head><script src="https://backstagephp.com/script.js"></script></head><body></body></html>', 200),
        'https://backstagephp.com/script.js' => Http::response($largeBody, 200, ['Content-Length' => '1000001']),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());
    
    $check->url = 'vormkracht10.nl';

    $largeResponse = Http::get('https://backstagephp.com/script.js');
    
    $this->assertGreaterThan(1000000, strlen($largeResponse->body()));
    
    $this->assertFalse($check->check($largeResponse, $crawler));
});

it('can perform the Javascript size check on a page with a Javascript file smaller than 1 MB', function () {
    $check = new JavascriptSizeCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head><script src="https://backstagephp.com/script.js"></script></head><body></body></html>', 200),
        'https://backstagephp.com/script.js' => Http::response('abcdefghij', 200, ['Content-Length' => '10']),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());
    
    // Set the URL property that the check needs
    $check->url = 'vormkracht10.nl';

    $this->assertTrue($check->check(Http::get('vormkracht10.nl/script.js'), $crawler));
});

it('can perform the Javascript size check on a page without Javascript files', function () {
    $check = new JavascriptSizeCheck;
    $crawler = new Crawler;

    Http::fake([
        'vormkracht10.nl' => Http::response('<html><head></head><body></body></html>', 200),
    ]);

    $crawler->addHtmlContent(Http::get('vormkracht10.nl')->body());
    
    $check->url = 'vormkracht10.nl';

    $this->assertTrue($check->check(Http::get('vormkracht10.nl'), $crawler));
});
