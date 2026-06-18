<?php

namespace Backstage\Seo\Checks\Configuration;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use vipnytt\RobotsTxtParser\TxtClient;

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

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(Response $response, Crawler $crawler): bool
    {
        $url = $this->url ?? ($response->transferStats?->getHandlerStats()['url'] ?? null);

        if (! $url) {
            $this->failureReason = __('failed.configuration.robots.missing_url');

            return false;
        }

        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);

        if (empty($parts['host'])) {
            $this->failureReason = __('failed.configuration.robots.missing_url');

            return false;
        }

        $base = ($parts['scheme'] ?? 'https').'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');

        // Fetch robots.txt through Laravel's HTTP client (rather than vipnytt's
        // own UriClient) so it can be faked in tests and stays consistent with
        // the other robots-aware checks. TxtClient then parses the body without
        // making any further network call.
        $robots = Http::get($base.'/robots.txt');

        $client = new TxtClient($base, $robots->status(), (string) $robots->body());

        if (! $client->userAgent('Googlebot')->isAllowed($url)) {
            $this->failureReason = __('failed.configuration.robots.disallowed');

            return false;
        }

        return true;
    }
}
