<?php

namespace Backstage\Seo\Checks\Configuration;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class NoFollowCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = "The page does not have 'nofollow' set";

    public string $description = "When the page has the 'nofollow' tag or meta tag set, search engines will not follow the links on the page.";

    public string $priority = 'low';

    public int $timeToFix = 5;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = false;

    public ?string $failureReason = null;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(): void(Response $response, Crawler $crawler): bool
    {
        if ($response->header('X-Robots-Tag') === 'nofollow') {
            $this->failureReason = __('failed.configuration.nofollow.tag');

            return false;
        }
        return $this->validateContent($crawler);
    }

    public function validateContent(): void(Crawler $crawler): bool
    {
        if (! $crawler->filterXPath('//meta[@name="robots"]')->getNode(0) &&
            ! $crawler->filterXPath('//meta[@name="googlebot"]')->getNode(0)
        ) {
            return true;
        }

        $robotContent = $crawler->filterXPath('//meta[@name="robots"]')->each(fn(Crawler $crawler, $i): ?string => $crawler->attr('content'));

        $googlebotContent = $crawler->filterXPath('//meta[@name="googlebot"]')->each(fn(Crawler $crawler, $i): ?string => $crawler->attr('content'));

        $content = array_merge($robotContent, $googlebotContent);

        foreach ($content as $tag) {
            if (str_contains((string) $tag, 'nofollow')) {
                $this->failureReason = __('failed.configuration.nofollow.meta');

                return false;
            }
        }

        return true;
    }
}
