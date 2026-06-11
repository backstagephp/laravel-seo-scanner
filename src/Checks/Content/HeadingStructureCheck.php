<?php

namespace Backstage\Seo\Checks\Content;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class HeadingStructureCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page has a logical heading structure';

    public string $description = 'Headings should not skip levels (for example an h2 followed by an h4) because a logical heading structure helps search engines and assistive technologies understand the page.';

    public string $priority = 'low';

    public int $timeToFix = 15;

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
        $headings = $crawler->filterXPath('//h1|//h2|//h3|//h4|//h5|//h6')->each(function (Crawler $node, $i) {
            return strtolower($node->nodeName());
        });

        if (count($headings) === 0) {
            return true;
        }

        $skips = [];
        $previousLevel = null;
        $previousTag = null;

        foreach ($headings as $tag) {
            $level = (int) substr($tag, 1);

            if ($previousLevel !== null && $level - $previousLevel > 1) {
                $skips[] = strtoupper($previousTag).' → '.strtoupper($tag);
            }

            $previousLevel = $level;
            $previousTag = $tag;
        }

        $this->actualValue = $skips;

        if (count($skips) > 0) {
            $this->failureReason = __('failed.content.heading_structure.skipped', [
                'actualValue' => implode(', ', $skips),
            ]);

            return false;
        }

        return true;
    }
}
