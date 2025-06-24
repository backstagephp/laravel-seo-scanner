<?php

namespace Backstage\Seo\Checks\Performance;

use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;
use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;

class ImageSizeCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'Images are not larger than 1 MB';

    public string $description = 'Images are not larger than 1 MB because this will slow down the page load time.';

    public string $priority = 'high';

    public int $timeToFix = 10;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = 1000000;

    public function check(Response $response, Crawler $crawler): bool
    {
        $this->expectedValue = bytesToHumanReadable($this->expectedValue);

        if (! $this->validateContent($crawler)) {
            return false;
        }

        return true;
    }

    public function validateContent(Crawler $crawler): bool
    {
        $crawler = $crawler->filterXPath('//img')->each(function (Crawler $node, $i) {
            return $node->attr('src');
        });

        $content = collect($crawler)->filter(fn ($value) => $value !== null)->toArray();

        if (! $content) {
            return true;
        }

        $links = [];

        $tooBigOrFailedLinks = collect($content)->filter(function ($url) use (&$links) {
            if (! str_contains($url, 'http')) {
                $url = url($url);
            }

            if (isBrokenLink($url)) {
                return true;
            }

            $imageSize = getRemoteFileSize($url);

            if ($imageSize > 1000000) {
                $size = bytesToHumanReadable($imageSize);

                $links[] = $url.' (size: '.$size.')';

                return true;
            }

            return false;
        })->toArray();

        if (! empty($tooBigOrFailedLinks) && count($links) > 0) {
            $this->actualValue = $links;

            $this->failureReason = __('failed.performance.image_size', [
                'actualValue' => implode(', ', $links),
                'expectedValue' => $this->expectedValue,
            ]);

            return false;
        }

        return true;
    }
}
