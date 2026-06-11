<?php

namespace Backstage\Seo\Checks\Meta;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class CanonicalCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page has a canonical URL';

    public string $description = 'The page should have a canonical URL because this tells search engines which version of a page is the preferred one and prevents duplicate content issues.';

    public string $priority = 'medium';

    public int $timeToFix = 5;

    public int $scoreWeight = 3;

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
        $link = $crawler->filterXPath('//link[@rel="canonical"]');

        if (! $link->count()) {
            $this->failureReason = __('failed.meta.canonical.missing');

            return false;
        }

        $href = $link->attr('href');

        $this->actualValue = $href;

        if (! $href) {
            $this->failureReason = __('failed.meta.canonical.missing');

            return false;
        }

        $href = addBaseIfRelativeUrl($href, $this->url);

        if (isBrokenLink($href)) {
            $this->failureReason = __('failed.meta.canonical.broken', [
                'actualValue' => $href,
            ]);

            return false;
        }

        return true;
    }
}
