<?php

namespace Backstage\Seo\Checks\PageSpeed;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Support\PageSpeed;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class PerformanceScoreCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page has a good PageSpeed performance score';

    public string $description = 'The page should have a Google PageSpeed (Lighthouse) performance score of at least 90 because this reflects a fast, well optimised page.';

    public string $priority = 'high';

    public int $timeToFix = 60;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = 90;

    public function check(Response $response, Crawler $crawler): bool
    {
        if (! PageSpeed::enabled()) {
            $this->failureReason = __('failed.pagespeed.not_configured');

            return false;
        }

        $data = PageSpeed::for($this->url);

        if ($data === null || $data['score'] === null) {
            $this->failureReason = __('failed.pagespeed.unavailable');

            return false;
        }

        $this->actualValue = $data['score'];

        if ($data['score'] >= $this->expectedValue) {
            return true;
        }

        $this->failureReason = __('failed.pagespeed.performance_score', [
            'actualValue' => $data['score'],
            'expectedValue' => $this->expectedValue,
        ]);

        return false;
    }
}
