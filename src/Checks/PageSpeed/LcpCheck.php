<?php

namespace Backstage\Seo\Checks\PageSpeed;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Support\PageSpeed;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class LcpCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The Largest Contentful Paint (LCP) is fast';

    public string $description = 'The Largest Contentful Paint (LCP) should be 2500 ms or less because this Core Web Vital measures how quickly the main content of the page becomes visible.';

    public string $priority = 'high';

    public int $timeToFix = 60;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = 2500;

    public function check(Response $response, Crawler $crawler): bool
    {
        if (! PageSpeed::enabled()) {
            $this->failureReason = __('failed.pagespeed.not_configured');

            return false;
        }

        $data = PageSpeed::for($this->url);

        if ($data === null || $data['lcp'] === null) {
            $this->failureReason = __('failed.pagespeed.unavailable');

            return false;
        }

        $lcp = (int) round($data['lcp']);

        $this->actualValue = $lcp;

        if ($lcp <= $this->expectedValue) {
            return true;
        }

        $this->failureReason = __('failed.pagespeed.lcp', [
            'actualValue' => $lcp,
            'expectedValue' => $this->expectedValue,
        ]);

        return false;
    }
}
