<?php

namespace Backstage\Seo\Checks\Meta;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class CharsetCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page has a character encoding declared';

    public string $description = 'The page should declare its character encoding (for example <meta charset="utf-8">) so browsers and search engines interpret the text correctly.';

    public string $priority = 'medium';

    public int $timeToFix = 1;

    public int $scoreWeight = 2;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(Response $response, Crawler $crawler): bool
    {
        if (! $this->validateContent($crawler)) {
            return false;
        }

        return true;
    }

    public function validateContent(Crawler $crawler): bool
    {
        $charset = $crawler->filterXPath('//meta[@charset]');

        if ($charset->count()) {
            $this->actualValue = $charset->attr('charset');

            return true;
        }

        $contentType = $crawler->filterXPath('//meta[@http-equiv="Content-Type"]');

        if ($contentType->count() && Str::contains(Str::lower($contentType->attr('content') ?? ''), 'charset=')) {
            $this->actualValue = $contentType->attr('content');

            return true;
        }

        $this->failureReason = __('failed.meta.charset.missing');

        return false;
    }
}
