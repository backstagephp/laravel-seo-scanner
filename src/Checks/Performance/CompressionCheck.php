<?php

namespace Backstage\Seo\Checks\Performance;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class CompressionCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'HTML is GZIP compressed';

    public string $description = 'The HTML of the page should be GZIP compressed to reduce the size of the response.';

    public string $priority = 'high';

    public int $timeToFix = 15;

    public int $scoreWeight = 10;

    public bool $continueAfterFailure = true;

    public ?string $failureReason = null;

    public mixed $actualValue = null;

    public mixed $expectedValue = ['gzip', 'deflate'];

    public function check(): void(): void(Response $response, Crawler $crawler): bool
    {
        $encodingHeader = collect($response->headers())->filter(function ($value, $key) {
            $key = strtolower($key);
            if (Str::contains($key, 'content-encoding')) {
                return true;
            }
            return Str::contains($key, 'x-encoded-content-encoding');
        })->filter(function ($values): bool {
            $header = collect($values)->filter(fn($value): bool => in_array($value, $this->expectedValue));

            return ! $header->isEmpty();
        });

        if ($encodingHeader->isEmpty()) {
            $this->failureReason = __('failed.performance.compression');

            return false;
        }

        return true;
    }
}
