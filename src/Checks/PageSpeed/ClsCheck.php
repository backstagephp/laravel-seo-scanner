<?php

namespace Backstage\Seo\Checks\PageSpeed;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Support\PageSpeed;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class ClsCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The Cumulative Layout Shift (CLS) is low';

    public string $description = 'The Cumulative Layout Shift (CLS) should be 0.1 or less because this Core Web Vital measures how much the page layout shifts unexpectedly while loading.';

    public string $priority = 'medium';

    public int $timeToFix = 60;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = 0.1;

    public function check(Response $response, Crawler $crawler): bool
    {
        if (! PageSpeed::enabled()) {
            $this->failureReason = __('failed.pagespeed.not_configured');

            return false;
        }

        $data = PageSpeed::for($this->url);

        if ($data === null || $data['cls'] === null) {
            $this->failureReason = __('failed.pagespeed.unavailable');

            return false;
        }

        $cls = round((float) $data['cls'], 3);

        $this->actualValue = $cls;

        if ($cls <= $this->expectedValue) {
            return true;
        }

        $this->failureReason = __('failed.pagespeed.cls', [
            'actualValue' => $cls,
            'expectedValue' => $this->expectedValue,
        ]);

        return false;
    }
}
