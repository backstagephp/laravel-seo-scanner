<?php

namespace Backstage\Seo\Checks\Meta;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class OpenGraphImageCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page has an Open Graph image';

    public string $description = 'The page should have an Open Graph image because this is the image that will be used when the page is shared on social media.';

    public string $priority = 'medium';

    public int $timeToFix = 20;

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
        $crawler = $crawler->filterXPath('//meta')->each(function (Crawler $node, $i) {
            $property = $node->attr('property');
            $content = $node->attr('content');

            if ($property === 'og:image') {
                return $content;
            }
        });

        $content = (string) collect($crawler)->first(fn ($value) => $value !== null);

        $this->actualValue = $content;

        if (! $content) {
            $this->failureReason = __('failed.meta.open_graph_image');

            return false;
        }

        $content = addBaseIfRelativeUrl($content, $this->url);

        if (isBrokenLink($content)) {
            $this->failureReason = __('failed.meta.open_graph_image.broken', [
                'actualValue' => $content,
            ]);

            return false;
        }

        return true;
    }
}
