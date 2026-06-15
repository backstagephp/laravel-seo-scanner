<?php

namespace Backstage\Seo\Checks\Ai;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class AiCrawlerCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'Known AI crawlers are not blocked in robots.txt';

    public string $description = 'The robots.txt file should not block known AI crawlers (such as GPTBot, ClaudeBot and PerplexityBot) unless that is intended, because blocking them removes the site from AI-powered search engines and assistants.';

    public string $priority = 'low';

    public int $timeToFix = 15;

    public int $scoreWeight = 1;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    /**
     * Well known AI crawler user agents.
     */
    protected array $aiCrawlers = [
        'GPTBot',
        'ChatGPT-User',
        'OAI-SearchBot',
        'ClaudeBot',
        'Claude-Web',
        'anthropic-ai',
        'PerplexityBot',
        'Google-Extended',
        'CCBot',
        'Applebot-Extended',
        'Bytespider',
        'Amazonbot',
        'Meta-ExternalAgent',
        'cohere-ai',
    ];

    public function check(Response $response, Crawler $crawler): bool
    {
        $base = $this->baseUrl($response);

        if (! $base) {
            return true;
        }

        $robots = Http::get($base.'/robots.txt');

        // No robots.txt (or an error) means nothing is disallowed.
        if (! $robots->successful() || trim((string) $robots->body()) === '') {
            return true;
        }

        $groups = $this->parseRobots((string) $robots->body());

        $blocked = [];

        foreach ($this->aiCrawlers as $crawlerName) {
            if ($this->isBlocked($crawlerName, $groups)) {
                $blocked[] = $crawlerName;
            }
        }

        if (count($blocked) === 0) {
            return true;
        }

        $this->actualValue = $blocked;

        $this->failureReason = __('failed.ai.ai_crawlers.blocked', [
            'actualValue' => implode(', ', $blocked),
        ]);

        return false;
    }

    protected function isBlocked(string $crawlerName, array $groups): bool
    {
        $needle = strtolower($crawlerName);

        foreach ($groups as $group) {
            if (! $group['disallowAll']) {
                continue;
            }

            if (in_array($needle, $group['agents'], true) || in_array('*', $group['agents'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse robots.txt into groups of user agents and whether they are fully disallowed.
     */
    protected function parseRobots(string $body): array
    {
        $groups = [];
        $index = -1;
        $lastLineWasAgent = false;

        foreach (preg_split('/\r\n|\r|\n/', $body) as $line) {
            $line = trim(preg_replace('/#.*$/', '', $line));

            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }

            [$field, $value] = explode(':', $line, 2);
            $field = strtolower(trim($field));
            $value = trim($value);

            if ($field === 'user-agent') {
                if (! $lastLineWasAgent) {
                    $groups[] = ['agents' => [], 'disallowAll' => false];
                    $index = count($groups) - 1;
                }

                $groups[$index]['agents'][] = strtolower($value);
                $lastLineWasAgent = true;

                continue;
            }

            if ($field === 'disallow' && $index >= 0 && $value === '/') {
                $groups[$index]['disallowAll'] = true;
            }

            $lastLineWasAgent = false;
        }

        return $groups;
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
