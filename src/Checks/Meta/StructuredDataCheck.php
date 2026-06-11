<?php

namespace Backstage\Seo\Checks\Meta;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class StructuredDataCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page contains structured data';

    public string $description = 'The page should contain structured data (JSON-LD) because this helps search engines understand the content and can enable rich results.';

    public string $priority = 'low';

    public int $timeToFix = 20;

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
        $scripts = $crawler->filterXPath('//script[@type="application/ld+json"]')->each(function (Crawler $node, $i) {
            return $node->text();
        });

        if (count($scripts) === 0) {
            $this->failureReason = __('failed.meta.structured_data.missing');

            return false;
        }

        $this->actualValue = count($scripts);

        foreach ($scripts as $script) {
            if (trim($script) === '') {
                continue;
            }

            json_decode($script);

            if (json_last_error() === JSON_ERROR_NONE) {
                return true;
            }
        }

        $this->failureReason = __('failed.meta.structured_data.invalid');

        return false;
    }
}
