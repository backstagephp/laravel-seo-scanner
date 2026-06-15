<?php

namespace Backstage\Seo\Checks\Security;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class HttpsCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page is served over HTTPS';

    public string $description = 'The page should be served over HTTPS because it encrypts traffic, is required for modern browser features and is a confirmed Google ranking signal.';

    public string $priority = 'high';

    public int $timeToFix = 30;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = 'https';

    public function check(Response $response, Crawler $crawler): bool
    {
        $url = $response->transferStats?->getHandlerStats()['url'] ?? $this->url;

        $scheme = $url ? strtolower((string) parse_url($url, PHP_URL_SCHEME)) : null;

        $this->actualValue = $scheme ?: 'unknown';

        if ($scheme === 'https') {
            return true;
        }

        $this->failureReason = __('failed.security.https.insecure', [
            'actualValue' => $this->actualValue,
        ]);

        return false;
    }
}
