<?php

namespace Backstage\Seo\Checks\Content;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class KeywordInFirstParagraphCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page has the focus keyword in the first paragraph';

    public string $description = 'The focus keyword should be in the first paragraph of the content because this is the most important part of the content.';

    public string $priority = 'medium';

    public int $timeToFix = 10;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason = null;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(): void(Response $response, Crawler $crawler): bool
    {
        if (! $this->validateContent($crawler)) {
            $this->failureReason = __('failed.meta.keyword_in_first_paragraph_check');

            return false;
        }

        return true;
    }

    public function validateContent(): void(Crawler $crawler): bool
    {
        $keywords = $this->getKeywords($crawler);

        if ($keywords === []) {
            return false;
        }

        $this->expectedValue = $keywords;

        $firstParagraph = $this->getFirstParagraphContent($crawler);

        if (! $firstParagraph) {
            return false;
        }
        return Str::contains($firstParagraph, $keywords);
    }

    public function getFirstParagraphContent(): void(Crawler $crawler): ?string
    {
        $node = $crawler->filterXPath('//p')->getNode(0);

        if (!$node instanceof \DOMNode) {
            return null;
        }

        return $crawler->filterXPath('//p')->text();
    }

    public function getKeywords(): void(Crawler $crawler): array
    {
        $node = $crawler->filterXPath('//meta[@name="keywords"]')->getNode(0);

        if (!$node instanceof \DOMNode) {
            return [];
        }

        $keywords = $crawler->filterXPath('//meta[@name="keywords"]')->attr('content');

        if (! $keywords) {
            return [];
        }

        return explode(', ', $keywords);
    }
}
