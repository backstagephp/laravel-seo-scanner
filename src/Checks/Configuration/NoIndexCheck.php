<?php

namespace Vormkracht10\Seo\Checks\Configuration;

use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;
use Vormkracht10\Seo\Interfaces\Check;
use Vormkracht10\Seo\Traits\PerformCheck;

class NoIndexCheck implements Check
{
    use PerformCheck;

    public string $title = "The page does not have 'noindex' set";

    public string $priority = 'low';

    public int $timeToFix = 5;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = false;

    public function check(Response $response): bool
    {
        $content = $this->getContentToValidate($response);

        if ($response->header('X-Robots-Tag') === 'noindex') {
            return false;
        }

        if (! $content) {
            return true;
        }

        if (! $this->validateContent($content)) {
            return false;
        }

        return true;
    }

    public function getContentToValidate(Response $response): array|null
    {
        $response = $response->body();

        $crawler = new Crawler($response);

        $robotContent = $crawler->filterXPath('//meta[@name="robots"]')->each(function (Crawler $node, $i) {
            return $node->attr('content');
        });

        $googlebotContent = $crawler->filterXPath('//meta[@name="googlebot"]')->each(function (Crawler $node, $i) {
            return $node->attr('content');
        });

        $content = array_merge($robotContent, $googlebotContent);

        return $content;
    }

    public function validateContent(array $content): bool
    {
        foreach ($content as $metaTag) {
            if (str_contains($metaTag, 'noindex')) {
                return false;
            }
        }

        return true;
    }
}
