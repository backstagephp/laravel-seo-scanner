<?php

namespace Backstage\Seo\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Throwable;

class JavascriptRenderer
{
    /**
     * Render a URL with JavaScript executed, waiting for the page to settle.
     *
     * On a render timeout/failure, falls back to an immediate render and then
     * to the raw HTTP response body, unless fallback is disabled in config.
     */
    public function render(string $url, Response $rawResponse): string
    {
        try {
            return $this->capture($url, wait: true);
        } catch (Throwable $e) {
            if (! config('seo.javascript_wait.fallback_on_timeout', true)) {
                throw $e;
            }

            Log::warning("SEO scanner: JavaScript render failed for `{$url}`, falling back to an immediate render.", ['exception' => $e->getMessage()]);

            try {
                return $this->capture($url, wait: false);
            } catch (Throwable $inner) {
                Log::warning("SEO scanner: immediate JavaScript render also failed for `{$url}`, falling back to the raw HTTP response.", ['exception' => $inner->getMessage()]);

                return $rawResponse->body();
            }
        }
    }

    /**
     * Capture the rendered HTML for a URL.
     *
     * When $wait is true, the configured wait strategy and timeout are applied.
     */
    protected function capture(string $url, bool $wait): string
    {
        $browsershot = $this->newBrowsershot($url);

        if ($wait) {
            $browsershot = $this->applyWaitStrategy($browsershot);
        }

        return $browsershot->bodyHtml();
    }

    /**
     * Apply the configured wait strategy and timeout to a Browsershot instance.
     */
    public function applyWaitStrategy(Browsershot $browsershot): Browsershot
    {
        $strategy = config('seo.javascript_wait.strategy', 'networkidle2');
        $timeout = (int) config('seo.javascript_wait.timeout', 15);

        $browsershot->timeout($timeout);

        return match ($strategy) {
            'networkidle0' => $browsershot->waitUntilNetworkIdle(strict: true),
            'delay' => $browsershot->setDelay((int) config('seo.javascript_wait.delay', 3000)),
            default => $browsershot->waitUntilNetworkIdle(strict: false),
        };
    }

    protected function newBrowsershot(string $url): Browsershot
    {
        return Browsershot::url($url);
    }
}
