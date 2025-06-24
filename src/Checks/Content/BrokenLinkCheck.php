<?php

namespace Backstage\Seo\Checks\Content;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class BrokenLinkCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page contains no broken links';

    public string $description = 'The page should not contain any broken links because it is bad for the user experience.';

    public string $priority = 'medium';

    public int $timeToFix = 10;

    public int $scoreWeight = 5;

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
        $content = $crawler->filterXPath('//a')->each(function (Crawler $node, $i) {
            return $node->attr('href');
        });

        if (! $content) {
            return true;
        }

        $content = collect($content)->filter(fn ($value) => $value !== null)
            ->map(fn ($link) => addBaseIfRelativeUrl($link, $this->url))
            ->filter(function ($link) {
                return $this->isValidLink($link) && ! $this->isExcludedLink($link);
            })
            ->filter(function ($link) {
                return isBrokenLink($link) ? $link : false;
            })->map(function ($link) {
                return [
                    'url' => $link,
                    'status' => (string) getRemoteStatus($link),
                ];
            })
            ->all();

        $this->actualValue = $content;

        if (count($content) > 0) {
            $failureReasons = collect($content)->map(function ($link) {
                return $link['url'].' ('.$link['status'].')';
            })->implode(', ');

            $this->failureReason = __('failed.content.broken_links', [
                'actualValue' => $failureReasons,
            ]);

            return false;
        }

        return true;
    }

    private function isValidLink($link): bool
    {
        return ! preg_match('/^mailto:/msi', $link) &&
               ! preg_match('/^tel:/msi', $link) &&
               filter_var($link, FILTER_VALIDATE_URL) !== false;
    }

    private function isExcludedLink($link): bool
    {
        $excludedPaths = config('seo.broken_link_check.exclude_links');
        if (empty($excludedPaths)) {
            return false;
        }

        foreach ($excludedPaths as $path) {
            if ($this->linkMatchesPath($link, $path)) {
                return true;
            }
        }

        return false;
    }

    private function linkMatchesPath($link, $path): bool
    {
        if (str_contains($path, '*')) {
            $path = str_replace('/*', '', $path);

            return str_starts_with($link, $path);
        }

        return str_contains($link, $path);
    }
}
