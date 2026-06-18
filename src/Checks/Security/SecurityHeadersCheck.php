<?php

namespace Backstage\Seo\Checks\Security;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class SecurityHeadersCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page sets recommended security headers';

    public string $description = 'The page should set security headers such as Strict-Transport-Security, X-Content-Type-Options, X-Frame-Options and Referrer-Policy because they protect visitors and signal a well maintained, trustworthy site.';

    public string $priority = 'medium';

    public int $timeToFix = 20;

    public int $scoreWeight = 2;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    /**
     * The security headers that are expected to be present.
     */
    protected array $expectedHeaders = [
        'Strict-Transport-Security',
        'X-Content-Type-Options',
        'X-Frame-Options',
        'Referrer-Policy',
    ];

    public function check(Response $response, Crawler $crawler): bool
    {
        $missing = [];

        foreach ($this->expectedHeaders as $header) {
            if (trim((string) $response->header($header)) === '') {
                $missing[] = $header;
            }
        }

        if (count($missing) === 0) {
            return true;
        }

        $this->actualValue = $missing;

        $this->failureReason = __('failed.security.headers.missing', [
            'actualValue' => implode(', ', $missing),
        ]);

        return false;
    }
}
