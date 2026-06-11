<?php

namespace Backstage\Seo\Checks\Meta;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class FaviconCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page has a favicon';

    public string $description = 'The page should reference a favicon because it helps users recognise the site in browser tabs, bookmarks and search results.';

    public string $priority = 'low';

    public int $timeToFix = 5;

    public int $scoreWeight = 2;

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
        $link = $crawler->filterXPath('//link[contains(@rel, "icon")]');

        if (! $link->count()) {
            $this->failureReason = __('failed.meta.favicon.missing');

            return false;
        }

        $href = $link->attr('href');

        $this->actualValue = $href;

        if (! $href) {
            $this->failureReason = __('failed.meta.favicon.missing');

            return false;
        }

        $href = addBaseIfRelativeUrl($href, $this->url);

        if (isBrokenLink($href)) {
            $this->failureReason = __('failed.meta.favicon.broken', [
                'actualValue' => $href,
            ]);

            return false;
        }

        return true;
    }
}
