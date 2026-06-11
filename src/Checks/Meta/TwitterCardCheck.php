<?php

namespace Backstage\Seo\Checks\Meta;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class TwitterCardCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page has a Twitter card';

    public string $description = 'The page should define a twitter:card meta tag so it is presented as a rich card when shared on X (Twitter).';

    public string $priority = 'low';

    public int $timeToFix = 10;

    public int $scoreWeight = 2;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = 'twitter:card';

    public function check(Response $response, Crawler $crawler): bool
    {
        if (! $this->validateContent($crawler)) {
            return false;
        }

        return true;
    }

    public function validateContent(Crawler $crawler): bool
    {
        $names = $crawler->filterXPath('//meta[@name]')->each(function (Crawler $node, $i) {
            return $node->attr('name');
        });

        $properties = $crawler->filterXPath('//meta[@property]')->each(function (Crawler $node, $i) {
            return $node->attr('property');
        });

        $attributes = array_filter(array_merge($names, $properties));

        if (in_array('twitter:card', $attributes, true)) {
            $this->actualValue = 'twitter:card';

            return true;
        }

        $this->failureReason = __('failed.meta.twitter_card.missing');

        return false;
    }
}
