<?php

namespace Backstage\Seo\Checks\Meta;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class HreflangCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page has valid hreflang annotations';

    public string $description = 'When a page targets multiple languages or regions it should use valid hreflang annotations so search engines serve the correct localised version of the page.';

    public string $priority = 'low';

    public int $timeToFix = 20;

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
        $hreflangs = $crawler->filterXPath('//link[@rel="alternate"][@hreflang]')->each(function (Crawler $node, $i) {
            return $node->attr('hreflang');
        });

        $hreflangs = array_filter($hreflangs);

        // No hreflang annotations means the page does not target multiple
        // languages, which should not be penalised.
        if (count($hreflangs) === 0) {
            return true;
        }

        $invalid = [];

        foreach ($hreflangs as $hreflang) {
            if (! preg_match('/^([a-z]{2,3}(-[a-z0-9]{2,8})*|x-default)$/i', $hreflang)) {
                $invalid[] = $hreflang;
            }
        }

        $this->actualValue = $invalid;

        if (count($invalid) > 0) {
            $this->failureReason = __('failed.meta.hreflang.invalid', [
                'actualValue' => implode(', ', $invalid),
            ]);

            return false;
        }

        return true;
    }
}
