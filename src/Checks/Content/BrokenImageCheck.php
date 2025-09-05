<?php

namespace Backstage\Seo\Checks\Content;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class BrokenImageCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page contains no broken images';

    public string $description = 'The page should not contain any broken images because it is bad for the user experience.';

    public string $priority = 'medium';

    public int $timeToFix = 10;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public $actualValue = null; // Missing type hint

    public $expectedValue = null; // Missing type hint

    public function check(Response $response, Crawler $crawler): bool
    {
        if (! $this->validateContent($crawler)) {
            return false;
        }

        return true;
    }

    public function validateContent(Crawler $crawler): bool
    {
        $content = $crawler->filterXPath('//img')->each(function (Crawler $node, $i) {
            return $node->attr('src');
        });

        if (! $content) {
            return true;
        }

        $links = [];

        $content = collect($content)->filter(fn ($value) => $value !== null)
            ->map(fn ($link) => addBaseIfRelativeUrl($link, $this->url))
            ->filter(fn ($link) => isBrokenLink($link))
            ->map(function ($link) use (&$links) {

                $remoteStatus = getRemoteStatus($link);

                $links[] = $link.' (status: '.$remoteStatus.')';

                return $link;
            });

        $this->actualValue = $links;

        if (count($content) > 0) {
            $this->failureReason = __('failed.content.broken_images', [
                'actualValue' => implode(', ', $links),
            ]);

            return false;
        }

        return true;
    }
}
