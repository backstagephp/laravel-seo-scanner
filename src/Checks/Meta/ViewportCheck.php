<?php

namespace Backstage\Seo\Checks\Meta;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class ViewportCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page has a valid viewport meta tag';

    public string $description = 'The page should have a viewport meta tag with width=device-width so it renders correctly on mobile devices, which is important for mobile-first indexing.';

    public string $priority = 'high';

    public int $timeToFix = 5;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = 'width=device-width';

    public function check(Response $response, Crawler $crawler): bool
    {
        if (! $this->validateContent($crawler)) {
            return false;
        }

        return true;
    }

    public function validateContent(Crawler $crawler): bool
    {
        $viewport = $crawler->filterXPath('//meta[@name="viewport"]');

        if (! $viewport->count()) {
            $this->failureReason = __('failed.meta.viewport.missing');

            return false;
        }

        $content = $viewport->attr('content') ?? '';

        $this->actualValue = $content;

        if (! Str::contains(Str::lower($content), 'width=device-width')) {
            $this->failureReason = __('failed.meta.viewport.invalid', [
                'actualValue' => $content,
            ]);

            return false;
        }

        return true;
    }
}
