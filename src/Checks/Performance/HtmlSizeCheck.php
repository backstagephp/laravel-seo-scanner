<?php

namespace Backstage\Seo\Checks\Performance;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class HtmlSizeCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'HTML is not larger than 100 KB';

    public string $description = 'HTML is not larger than 100 KB because this will slow down the page load time.';

    public string $priority = 'medium';

    public int $timeToFix = 60;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = 100000;

    public function check(Response $response, Crawler $crawler): bool
    {
        $this->expectedValue = bytesToHumanReadable($this->expectedValue);

        if (! $this->validateContent($response)) {
            return false;
        }

        return true;
    }

    public function validateContent(Response $response): bool
    {
        $content = $response->body();

        if (! $content) {
            return false;
        }

        if (strlen($content) > 100000) {
            $this->actualValue = strlen($content);

            $this->failureReason = __('failed.performance.html_size', [
                'actualValue' => bytesToHumanReadable($this->actualValue),
                'expectedValue' => $this->expectedValue,
            ]);

            return false;
        }

        return true;
    }
}
