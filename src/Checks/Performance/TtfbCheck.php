<?php

namespace Backstage\Seo\Checks\Performance;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class TtfbCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'Time To First Byte (TTFB) is below 600 ms';

    public string $description = 'The Time To First Byte (TTFB) should be below 600 ms because this will improve the page load time.';

    public string $priority = 'high';

    public int $timeToFix = 15;

    public int $scoreWeight = 10;

    public bool $continueAfterFailure = true;

    public ?string $failureReason = null;

    public mixed $actualValue = null;

    public mixed $expectedValue = 0.6;

    public function check(): void(): void(Response $response, Crawler $crawler): bool
    {
        $ttfb = $response->transferStats?->getHandlerStats()['starttransfer_time'] ?? null;

        if (! $ttfb) {
            $this->failureReason = __('failed.performance.ttfb.missing');

            return false;
        }

        $this->actualValue = round($ttfb, 2);

        if ($this->actualValue <= $this->expectedValue) {
            return true;
        }

        $this->failureReason = __('failed.performance.ttfb', [
            'actualValue' => $this->actualValue,
            'expectedValue' => $this->expectedValue,
        ]);

        return false;
    }
}
