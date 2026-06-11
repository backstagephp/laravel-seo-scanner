<?php

namespace Backstage\Seo\Checks\Content;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class ImageDimensionsCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'Images have width and height attributes';

    public string $description = 'Images should have explicit width and height attributes because this reserves space during loading and prevents layout shift (CLS).';

    public string $priority = 'low';

    public int $timeToFix = 10;

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
        $images = $crawler->filterXPath('//img')->each(function (Crawler $node, $i) {
            $src = $node->attr('src');

            if (! $src) {
                return null;
            }

            $width = $node->attr('width');
            $height = $node->attr('height');

            if ($width === null || $width === '' || $height === null || $height === '') {
                return $src;
            }

            return null;
        });

        $images = array_values(array_filter($images));

        $this->actualValue = $images;

        if (count($images) > 0) {
            $this->failureReason = __('failed.content.image_dimensions.missing', [
                'actualValue' => implode(', ', $images),
            ]);

            return false;
        }

        return true;
    }
}
