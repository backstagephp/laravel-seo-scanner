<?php

namespace Backstage\Seo\Checks\Configuration;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;
use vipnytt\RobotsTxtParser\UriClient;

class RobotsCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'Robots.txt allows indexing';

    public string $description = 'The robots.txt file should allow indexing of the page.';

    public string $priority = 'low';

    public int $timeToFix = 5;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = false;

    public ?string $failureReason = null;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(): void(Response $response, Crawler $crawler): bool
    {
        $url = $response->transferStats?->getHandlerStats()['url'] ?? null;

        if (! $url) {
            $this->failureReason = __('failed.configuration.robots.missing_url');

            return false;
        }

        $uriClient = new UriClient($url);

        if (! $uriClient->userAgent('Googlebot')->isAllowed($url)) {
            $this->failureReason = __('failed.configuration.robots.disallowed');

            return false;
        }

        return true;
    }
}
