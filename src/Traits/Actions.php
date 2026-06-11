<?php

namespace Backstage\Seo\Traits;

use Illuminate\Http\Client\Response;
use Readability\Readability;
use Symfony\Component\DomCrawler\Crawler;

trait Actions
{
    private function getTextContent(Response $response, Crawler $crawler): string
    {
        $body = $response->body();

        if ($this->useJavascript) {
            $body = $crawler->filter('body')->html();
        }

        try {
            // Tidy is disabled because recent libtidy/PHP versions reject the
            // tidy configuration that the readability library passes.
            $readability = new Readability($body, null, 'libxml', false);
            $readability->init();

            return $readability->getContent()->textContent;
        } catch (\Throwable $e) {
            // If Readability fails, fall back to extracting text from the body
            // Remove HTML tags and return plain text
            return strip_tags($body);
        }
    }

    private function extractPhrases(string $content): array
    {
        // Get phrases seperate by new line, dot, exclamation mark or question mark
        return preg_split('/\n|\.|\!|\?/', $content);
    }
}
