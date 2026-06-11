<?php

namespace Backstage\Seo\Checks\Performance;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class ModernImageFormatCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'Images use modern formats';

    public string $description = 'Images should use modern formats such as WebP or AVIF (or be served via a <picture> element with a modern source) because they are significantly smaller than JPEG and PNG, which improves load times.';

    public string $priority = 'low';

    public int $timeToFix = 20;

    public int $scoreWeight = 2;

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
        $legacy = $crawler->filterXPath('//img[not(ancestor::picture)]')->each(function (Crawler $node, $i) {
            $src = $node->attr('src');

            if (! $src) {
                return null;
            }

            // An img offering a modern format through srcset is considered fine.
            $srcset = (string) $node->attr('srcset');

            if (preg_match('/\.(webp|avif)/i', $srcset)) {
                return null;
            }

            if (preg_match('/\.(jpe?g|png|gif|bmp)(\?|#|$)/i', $src)) {
                return $src;
            }

            return null;
        });

        $legacy = array_values(array_filter($legacy));

        $this->actualValue = $legacy;

        if (count($legacy) > 0) {
            $this->failureReason = __('failed.performance.modern_image_format', [
                'actualValue' => implode(', ', $legacy),
            ]);

            return false;
        }

        return true;
    }
}
