<?php

namespace Vormkracht10\Seo\Checks\Meta;

use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;
use Vormkracht10\Seo\Interfaces\Check;
use Vormkracht10\Seo\Traits\PerformCheck;

class DescriptionCheck implements Check
{
    use PerformCheck;

    public string $title = 'The page has a meta description';

    public string $priority = 'medium';

    public int $timeToFix = 1;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public function check(Response $response): bool
    {
        $content = $this->getContentToValidate($response);

        if (! $content || ! $this->validateContent($content)) {
            return false;
        }

        return true;
    }

    public function getContentToValidate(Response $response): string|null
    {
        $response = $response->body();

        $crawler = new Crawler($response);

        return $crawler->filterXPath('//meta[@name="description"]')->attr('content');
    }

    public function validateContent(string $content): bool
    {
        return strlen($content) > 0;
    }
}
