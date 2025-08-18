<?php

namespace Backstage\Seo\Checks\Meta;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class LangCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The lang attribute is set on the html tag';

    public string $description = 'The lang attribute should be set because this is used by search engines to determine the language of the page.';

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
            return false;
        }

        return true;
    }

    public function validateContent(Crawler $crawler): bool
    {
        $node = $crawler->filterXPath('//html')->getNode(0);

        if (! $node) {
            $this->failureReason = __('failed.meta.no_lang');

            return false;
        }

        $lang = $crawler->filterXPath('//html')->attr('lang');

        if (! $lang) {
            $this->failureReason = __('failed.meta.no_lang');

            return false;
        }

        return true;
    }
}
