<?php

namespace Backstage\Seo\Tests\Support;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

/**
 * A check that always throws, used to verify that a single misbehaving check
 * (or a misbehaving dependency it calls) cannot abort the whole scan.
 */
class ThrowingCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'Throwing check';

    public string $description = 'A check that throws to exercise error isolation.';

    public string $priority = 'low';

    public int $timeToFix = 0;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(Response $response, Crawler $crawler): bool
    {
        throw new \RuntimeException('boom');
    }
}
