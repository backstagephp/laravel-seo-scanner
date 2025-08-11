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
            $readability = new Readability($body);
            $readability->init();

            return $readability->getContent()->textContent;
        } catch (\Exception $e) {
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
