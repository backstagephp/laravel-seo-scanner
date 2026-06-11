<?php

namespace Backstage\Seo\Checks\Content;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class LazyLoadingCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'Images below the fold use lazy loading';

    public string $description = 'Images below the fold should use loading="lazy" because this defers offscreen image loading and improves the initial page load performance.';

    public string $priority = 'low';

    public int $timeToFix = 10;

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
        $images = $crawler->filterXPath('//img')->each(function (Crawler $node, $i) {
            return [
                'src' => $node->attr('src'),
                'loading' => $node->attr('loading'),
            ];
        });

        // The first image is typically above the fold and may load eagerly.
        $belowTheFold = array_slice($images, 1);

        if (count($belowTheFold) === 0) {
            return true;
        }

        $missing = [];

        foreach ($belowTheFold as $image) {
            if (! $image['src']) {
                continue;
            }

            if (strtolower((string) $image['loading']) !== 'lazy') {
                $missing[] = $image['src'];
            }
        }

        $this->actualValue = $missing;

        if (count($missing) > 0) {
            $this->failureReason = __('failed.content.lazy_loading.missing', [
                'actualValue' => implode(', ', $missing),
            ]);

            return false;
        }

        return true;
    }
}
