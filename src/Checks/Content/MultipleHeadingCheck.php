<?php

namespace Backstage\Seo\Checks\Content;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class MultipleHeadingCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page has an H1 tag and if it is used only once per page';

    public string $description = 'The page should have only one H1 tag because there should be only one main heading on the page. The H1 tag should be used to describe the main topic of the page. The H1 tag is also used by search engines to determine the topic of the page.';

    public string $priority = 'low';

    public int $timeToFix = 1;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(Response $response, Crawler $crawler): bool
    {
        if (! $this->validateContent($crawler)) {
            return false;
        }

        return true;
    }

    public function validateContent(Crawler $crawler): bool
    {
        $content = $crawler->filterXPath('//h1')->each(function (Crawler $node, $i) {
            return $node->text();
        });

        if (! $content) {
            $this->failureReason = __('failed.content.no_heading');

            return false;
        }

        if (is_array($content) && count($content) > 1) {
            $this->actualValue = $content;

            $this->failureReason = __('failed.content.multiple_h1', [
                'actualValue' => implode(', ', $this->actualValue),
            ]);

            return false;
        }

        return true;
    }
}
