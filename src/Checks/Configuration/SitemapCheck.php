<?php

namespace Backstage\Seo\Checks\Configuration;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class SitemapCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The site has a valid XML sitemap';

    public string $description = 'The site should have a valid XML sitemap because it helps search engines discover and crawl all important pages efficiently.';

    public string $priority = 'medium';

    public int $timeToFix = 20;

    public int $scoreWeight = 3;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(Response $response, Crawler $crawler): bool
    {
        $base = $this->baseUrl($response);

        if (! $base) {
            $this->failureReason = __('failed.configuration.sitemap.missing', ['actualValue' => '-']);

            return false;
        }

        $url = $this->sitemapUrlFromRobots($base) ?? $base.'/sitemap.xml';

        $this->actualValue = $url;

        $sitemap = Http::get($url);

        if (! $sitemap->successful() || trim((string) $sitemap->body()) === '') {
            $this->failureReason = __('failed.configuration.sitemap.missing', ['actualValue' => $url]);

            return false;
        }

        if (! $this->isValidSitemap((string) $sitemap->body())) {
            $this->failureReason = __('failed.configuration.sitemap.invalid', ['actualValue' => $url]);

            return false;
        }

        return true;
    }

    protected function isValidSitemap(string $body): bool
    {
        return (bool) preg_match('/<\s*(urlset|sitemapindex)[\s>]/i', $body);
    }

    protected function sitemapUrlFromRobots(string $base): ?string
    {
        $robots = Http::get($base.'/robots.txt');

        if (! $robots->successful()) {
            return null;
        }

        foreach (preg_split('/\r\n|\r|\n/', (string) $robots->body()) as $line) {
            if (preg_match('/^\s*sitemap\s*:\s*(\S+)/i', $line, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
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
