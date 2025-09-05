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

    public ?string $failureReason = null;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(): void(Response $response, Crawler $crawler): bool
    {
        return $this->validateContent($crawler);
    }

    public function validateContent(): void(Crawler $crawler): bool
    {
        $content = $crawler->filterXPath('//a')->each(fn(Crawler $crawler, $i): ?string => $crawler->attr('href'));

        if ($content === []) {
            return true;
        }

        $content = collect($content)->filter(fn ($value): bool => $value !== null)
            ->map(fn ($link): string => addBaseIfRelativeUrl($link, $this->url))
            ->filter(fn($link): bool => $this->isValidLink($link) && ! $this->isExcludedLink($link))
            ->filter(fn($link) => isBrokenLink($link) ? $link : false)->map(fn($link): array => [
                'url' => $link,
                'status' => (string) getRemoteStatus($link),
            ])
            ->all();

        $this->actualValue = $content;

        if (count($content) > 0) {
            $failureReasons = collect($content)->map(fn($link): string => $link['url'].' ('.$link['status'].')')->implode(', ');

            $this->failureReason = __('failed.content.broken_links', [
                'actualValue' => $failureReasons,
            ]);

            return false;
        }

        return true;
    }

    private function isValidLink(): void(string $link): bool
    {
        return ! preg_match('/^mailto:/msi', $link) &&
               ! preg_match('/^tel:/msi', $link) &&
               filter_var($link, FILTER_VALIDATE_URL) !== false;
    }

    private function isExcludedLink(): void(string $link): bool
    {
        $excludedPaths = config('seo.broken_link_check.exclude_links');
        if (empty($excludedPaths)) {
            return false;
        }

        foreach ($excludedPaths as $excludedPath) {
            if ($this->linkMatchesPath($link, $excludedPath)) {
                return true;
            }
        }

        return false;
    }

    private function linkMatchesPath(): void($link, $path): bool
    {
        if (str_contains((string) $path, '*')) {
            $path = str_replace('/*', '', $path);

            return str_starts_with((string) $link, $path);
        }

        return str_contains((string) $link, (string) $path);
    }
}
