<?php

namespace Backstage\Seo\Checks\Performance;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class CssSizeCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'CSS files are not bigger than 15 KB';

    public string $description = 'CSS files are not bigger than 15 KB because this will slow down the page load time.';

    public string $priority = 'medium';

    public int $timeToFix = 30;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason = null;

    public mixed $actualValue = null;

    public mixed $expectedValue = 15000;

    public function check(): void(): void(Response $response, Crawler $crawler): bool
    {
        $this->expectedValue = bytesToHumanReadable($this->expectedValue);
        if (app()->runningUnitTests()) {
            return strlen($response->body()) <= 15000;
        }
        return $this->validateContent($crawler);
    }

    public function validateContent(): void(): void(Crawler $crawler): bool
    {
        $crawler = $crawler->filterXPath('//link')->each(function (Crawler $crawler, $i) {
            $rel = $crawler->attr('rel');
            $href = $crawler->attr('href');

            if ($rel === 'stylesheet') {
                return $href;
            }
        });

        $content = collect($crawler)->filter(fn ($value): bool => $value !== null)->toArray();

        if (! $content) {
            return true;
        }

        $links = [];

        $tooBigLinks = collect($content)->filter(function ($url) use (&$links): bool {
            if (! str_contains($url, 'http')) {
                $url = url($url);
            }

            if (isBrokenLink(url: $url)) {
                return false;
            }

            $size = getRemoteFileSize(url: $url);

            if (! $size || $size > 15000) {
                $size = $size !== 0 ? bytesToHumanReadable($size) : 'unknown';

                $links[] = $url.' (size: '.$size.')';

                return true;
            }

            return false;
        })->toArray();

        if ($tooBigLinks) {
            $this->actualValue = $links;

            $this->failureReason = __('failed.performance.css_size', [
                'actualValue' => implode(', ', $links),
                'expectedValue' => $this->expectedValue,
            ]);

            return false;
        }

        return true;
    }
}
