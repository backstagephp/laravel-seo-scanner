<?php

namespace Backstage\Seo\Checks\Meta;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class OpenGraphCompletenessCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page has complete Open Graph tags';

    public string $description = 'The page should define the core Open Graph tags (og:title, og:description, og:url and og:type) so it is presented correctly when shared on social media.';

    public string $priority = 'low';

    public int $timeToFix = 10;

    public int $scoreWeight = 3;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = ['og:title', 'og:description', 'og:url', 'og:type'];

    public function check(Response $response, Crawler $crawler): bool
    {
        if (! $this->validateContent($crawler)) {
            return false;
        }

        return true;
    }

    public function validateContent(Crawler $crawler): bool
    {
        $properties = $crawler->filterXPath('//meta[@property]')->each(function (Crawler $node, $i) {
            return $node->attr('property');
        });

        $properties = array_filter($properties);

        $missing = array_values(array_diff($this->expectedValue, $properties));

        $this->actualValue = $missing;

        if (count($missing) > 0) {
            $this->failureReason = __('failed.meta.open_graph.incomplete', [
                'actualValue' => implode(', ', $missing),
            ]);

            return false;
        }

        return true;
    }
}
