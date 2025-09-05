<?php

namespace Backstage\Seo\Checks\Content;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class AltTagCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'Every image has an alt tag';

    public string $description = 'Every image on the page should have an alt tag to describe the image.';

    public string $priority = 'low';

    public int $timeToFix = 5;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(Response $response, Crawler $crawler) // Missing return type
    {
        if (! $this->validateContent($crawler)) {
            return false;
        }

        return true;
    }

    public function validateContent(Crawler $crawler): bool
    {
        $imagesWithoutAlt = $crawler->filterXPath('//img[not(@alt)]')->each(function (Crawler $node, $i) {
            return $this->filterImage($node);
        });
        $imagesWithEmptyAlt = $crawler->filterXPath('//img[@alt=""]')->each(function (Crawler $node, $i) {
            return $this->filterImage($node);
        });

        // Remove null values from the arrays
        $imagesWithoutAlt = array_filter($imagesWithoutAlt);
        $imagesWithEmptyAlt = array_filter($imagesWithEmptyAlt);

        $imagesWithoutAlt = array_merge($imagesWithoutAlt, $imagesWithEmptyAlt);

        $this->actualValue = $imagesWithoutAlt;

        if (count($imagesWithoutAlt) > 0) {
            $this->failureReason = __('failed.content.alt_tag', [
                'actualValue' => implode(', ', $imagesWithoutAlt),
            ]);

            return false;
        }

        return true;
    }

    private function filterImage($node) // Missing parameter type and return type
    {
        $src = $node->attr('src');

        if (! $src) {
            return null;
        }

        if (str_contains($src, '.svg')) {
            return $src;
        }

        $src = addBaseIfRelativeUrl($src, $this->url);

        $dimensions = $this->getImageDimensions($src, $node);

        if ($dimensions['width'] < 5 || $dimensions['height'] < 5) {
            return null;
        }

        return $src;
    }

    private function getImageDimensions($src, $node) // Missing parameter types and return type
    {
        if (app()->runningUnitTests()) {
            return [
                'width' => $node->attr('width'),
                'height' => $node->attr('height'),
            ];
        }

        $dimensions = @getimagesize($src);

        if ($dimensions === false) {
            return [
                'width' => 0,
                'height' => 0,
            ];
        }

        return [
            'width' => $dimensions[0],
            'height' => $dimensions[1],
        ];
    }
}
