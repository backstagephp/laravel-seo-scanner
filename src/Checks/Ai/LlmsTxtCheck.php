<?php

namespace Backstage\Seo\Checks\Ai;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class LlmsTxtCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The site provides an llms.txt file';

    public string $description = 'The site should provide an llms.txt file because it gives large language models a curated, Markdown overview of the site, which improves how AI tools understand and cite the content.';

    public string $priority = 'low';

    public int $timeToFix = 30;

    public int $scoreWeight = 1;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(Response $response, Crawler $crawler): bool
    {
        $base = $this->baseUrl($response);

        if (! $base) {
            $this->failureReason = __('failed.ai.llms_txt.missing', ['actualValue' => '-']);

            return false;
        }

        $url = $base.'/llms.txt';

        $this->actualValue = $url;

        $llms = Http::get($url);

        if ($llms->successful() && trim((string) $llms->body()) !== '') {
            return true;
        }

        $this->failureReason = __('failed.ai.llms_txt.missing', ['actualValue' => $url]);

        return false;
    }

    protected function baseUrl(Response $response): ?string
    {
        $url = $this->url ?? ($response->transferStats?->getHandlerStats()['url'] ?? null);

        if (! $url) {
            return null;
        }

        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);

        if (empty($parts['host'])) {
            return null;
        }

        $scheme = $parts['scheme'] ?? 'https';

        return $scheme.'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
    }
}
