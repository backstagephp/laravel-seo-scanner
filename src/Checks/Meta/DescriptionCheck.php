<?php

namespace Backstage\Seo\Checks\Meta;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class DescriptionCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page has a meta description';

    public string $description = 'The meta description is used by search engines to show a description of the page in the search results.';

    public string $priority = 'medium';

    public int $timeToFix = 1;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(Response $response, Crawler $crawler): bool
    {
        if (! $this->validateContent($crawler)) {
            $this->failureReason = __('failed.meta.description');

            return false;
        }

        return true;
    }

    public function getDescriptionContent(Crawler $crawler): ?string
    {
        /** @var \DOMElement|null $node */
        $node = $crawler->filterXPath('//meta[@name="description"]')->getNode(0);

        if ($node && $node->hasAttribute('content')) {
            return $node->getAttribute('content');
        }

        return null;
    }

    public function validateContent(Crawler $crawler): bool
    {
        $content = $this->getDescriptionContent($crawler);

        return ! empty($content);
    }

    public function isDescriptionSet(Crawler $crawler): bool
    {
        return $this->getDescriptionContent($crawler) !== null;
    }
}
