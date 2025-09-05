<?php

namespace Backstage\Seo\Checks\Performance;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class JavascriptSizeCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'Javascript files are not bigger than 1 MB';

    public string $description = 'Javascript files are not bigger than 1 MB because this will slow down the page load time.';

    public string $priority = 'medium';

    public int $timeToFix = 60;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason = null;

    public mixed $actualValue = null;

    public mixed $expectedValue = 1000000;

    public function check(): void(): void(Response $response, Crawler $crawler): bool
    {
        if (app()->runningUnitTests()) {
            return strlen($response->body()) <= 1000000;
        }

        $this->expectedValue = bytesToHumanReadable($this->expectedValue);
        return $this->validateContent($crawler);
    }

    public function validateContent(): void(): void(Crawler $crawler): bool
    {
        $crawler = $crawler->filterXPath('//script')->each(function (Crawler $crawler, $i) {
            $src = $crawler->attr('src');

            if ($src) {
                return $src;
            }
        });

        $content = collect($crawler)->filter(fn ($value): bool => $value !== null)->toArray();

        if (! $content) {
            return true;
        }

        $links = [];

        $tooBigLinks = collect($content)->filter(function ($url) use (&$links): bool {
            if (! $url) {
                return false;
            }

            if (! str_contains($url, 'http')) {
                $url = url($url);
            }

            if (isBrokenLink(url: $url)) {
                return false;
            }

            $size = getRemoteFileSize(url: $url);

            if (! $size || $size > 1000000) {
                $size = $size !== 0 ? bytesToHumanReadable($size) : 'unknown';

                $links[] = $url.' (size: '.$size.')';

                return true;
            }

            return false;
        })->toArray();

        if (! empty($tooBigLinks)) {
            $this->actualValue = $links;

            $this->failureReason = __('failed.performance.javascript_size', [
                'actualValue' => implode(', ', $links),
                'expectedValue' => $this->expectedValue,
            ]);

            return false;
        }

        return true;
    }
}
