<?php

namespace Backstage\Seo\Checks\Content;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class MixedContentCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'All links redirect to an url using HTTPS';

    public string $description = 'All links on the page should redirect to an url using HTTPS instead of HTTP because this is more secure.';

    public string $priority = 'high';

    public int $timeToFix = 1;

    public int $scoreWeight = 5;

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
        $content = $crawler->filterXPath('//a')->each(function (Crawler $node, $i) {
            return $node->attr('href');
        });

        if (! $content) {
            return true;
        }

        $links = [];

        $nonSecureLinks = collect($content)->filter(function ($item) use (&$links) {
            if (preg_match('/^http:\/\//', $item)) {
                $links[] = $item;

                return true;
            }

            return false;
        });

        if ($nonSecureLinks->count() > 0) {
            $this->failureReason = __('failed.content.mixed_content', [
                'links' => implode(', ', $links),
            ]);

            return false;
        }

        return true;
    }
}
