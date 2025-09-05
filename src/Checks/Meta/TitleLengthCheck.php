<?php

namespace Backstage\Seo\Checks\Meta;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class TitleLengthCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page title is not longer than 60 characters';

    public string $description = 'The title of the page should not be longer than 60 characters because this is the maximum length that is shown in the search results.';

    public string $priority = 'medium';

    public int $timeToFix = 1;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason = null;

    public mixed $actualValue = null;

    public mixed $expectedValue = 60;

    public function check(): void(Response $response, Crawler $crawler): bool
    {
        return $this->validateContent($crawler);
    }

    public function validateContent(): void(Crawler $crawler): bool
    {
        $node = $crawler->filterXPath('//title')->getNode(0);

        if (!$node instanceof \DOMNode) {
            $this->failureReason = __('failed.content.no_title');

            return false;
        }

        $content = $crawler->filterXPath('//title')->text();

        if ($content === '' || $content === '0') {
            $this->failureReason = __('failed.content.no_title');

            return false;
        }

        if (strlen($content) > $this->expectedValue) {
            $this->failureReason = __('failed.content.title_length', [
                'actualValue' => strlen($content),
                'expectedValue' => $this->expectedValue,
            ]);

            return false;
        }

        return true;
    }
}
