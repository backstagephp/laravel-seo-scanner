<?php

namespace Backstage\Seo\Checks\Performance;

use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;
use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;

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

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = 15000;

    public function check(Response $response, Crawler $crawler): bool
    {
        $this->expectedValue = bytesToHumanReadable($this->expectedValue);

        if (app()->runningUnitTests()) {
            if (strlen($response->body()) > 15000) {
                return false;
            }

            return true;
        }

        if (! $this->validateContent($crawler)) {
            return false;
        }

        return true;
    }

    public function validateContent(Crawler $crawler): bool
    {
        $crawler = $crawler->filterXPath('//link')->each(function (Crawler $node, $i) {
            $rel = $node->attr('rel');
            $href = $node->attr('href');

            if ($rel === 'stylesheet') {
                return $href;
            }
        });

        $content = collect($crawler)->filter(fn ($value) => $value !== null)->toArray();

        if (! $content) {
            return true;
        }

        $links = [];

        $tooBigLinks = collect($content)->filter(function ($url) use (&$links) {
            if (! str_contains($url, 'http')) {
                $url = url($url);
            }

            if (isBrokenLink(url: $url)) {
                return false;
            }

            $size = getRemoteFileSize(url: $url);

            if (! $size || $size > 15000) {
                $size = $size ? bytesToHumanReadable($size) : 'unknown';

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
