<?php

namespace Backstage\Seo\Checks\Performance;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class ResponseCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page response returns a 200 status code';

    public string $description = 'The page response should return a 200 status code because this means the page is available.';

    public string $priority = 'high';

    public int $timeToFix = 10;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = false;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = 200;

    public function check(Response $response, Crawler $crawler): bool
    {
        $this->actualValue = $response->status();

        if ($response->status() === 200) {
            return true;
        }

        $this->failureReason = __('failed.performance.response', [
            'actualValue' => $this->actualValue,
            'expectedValue' => $this->expectedValue,
        ]);

        return false;
    }
}
