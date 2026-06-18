<?php

namespace Backstage\Seo\Checks\Security;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class RedirectChainCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page does not use a long redirect chain';

    public string $description = 'The page should be reachable without a long chain of redirects because each extra redirect adds latency and can dilute SEO signals.';

    public string $priority = 'low';

    public int $timeToFix = 15;

    public int $scoreWeight = 2;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = 1;

    public function check(Response $response, Crawler $crawler): bool
    {
        $redirects = (int) ($response->transferStats?->getHandlerStats()['redirect_count'] ?? 0);

        $this->actualValue = $redirects;

        if ($redirects <= 1) {
            return true;
        }

        $this->failureReason = __('failed.security.redirect_chain.too_long', [
            'actualValue' => $redirects,
            'expectedValue' => $this->expectedValue,
        ]);

        return false;
    }
}
