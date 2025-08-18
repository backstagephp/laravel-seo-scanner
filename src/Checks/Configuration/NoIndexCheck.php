<?php

namespace Backstage\Seo\Checks\Configuration;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class NoIndexCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = "The page does not have 'noindex' set";

    public string $description = "When the page has the 'noindex' tag or meta tag set, search engines will not index the page.";

    public string $priority = 'low';

    public int $timeToFix = 5;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = false;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(Response $response, Crawler $crawler): bool
    {
        if ($response->header('X-Robots-Tag') === 'noindex') {
            $this->failureReason = __('failed.configuration.noindex.tag');

            return false;
        }

        if (! $this->validateContent($crawler)) {
            return false;
        }

        return true;
    }

    public function validateContent(Crawler $crawler): bool
    {
        if (! $crawler->filterXPath('//meta[@name="robots"]')->getNode(0) &&
            ! $crawler->filterXPath('//meta[@name="googlebot"]')->getNode(0)
        ) {
            return true;
        }

        $robotContent = $crawler->filterXPath('//meta[@name="robots"]')->each(function (Crawler $node, $i) {
            return $node->attr('content');
        });

        $googlebotContent = $crawler->filterXPath('//meta[@name="googlebot"]')->each(function (Crawler $node, $i) {
            return $node->attr('content');
        });

        $content = array_merge($robotContent, $googlebotContent);

        foreach ($content as $tag) {
            if (str_contains($tag, 'noindex')) {
                $this->failureReason = __('failed.configuration.noindex.meta');

                return false;
            }
        }

        return true;
    }
}
