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

    public ?string $failureReason = null;

    public $actualValue: mixed; // Missing type hint

    public $expectedValue: mixed; // Missing type hint

    public function check(): void(): void(Response $response, Crawler $crawler): bool
    {
        return $this->validateContent($crawler);
    }

    public function validateContent(): void(): void(Crawler $crawler): bool
    {
        $content = $crawler->filterXPath('//img')->each(fn(Crawler $crawler, $i): ?string => $crawler->attr('src'));

        if ($content === []) {
            return true;
        }

        $links = [];

        $content = collect($content)->filter(fn ($value): bool => $value !== null)
            ->map(fn ($link): string => addBaseIfRelativeUrl($link, $this->url))
            ->filter(fn ($link): bool => isBrokenLink($link))
            ->map(function (string $link) use (&$links): string {

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
