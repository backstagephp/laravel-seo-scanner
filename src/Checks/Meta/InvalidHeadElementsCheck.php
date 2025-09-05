<?php

namespace Backstage\Seo\Checks\Meta;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class InvalidHeadElementsCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page does not contain invalid HTML elements in the head section';

    public string $description = 'The head section should not contain invalid HTML elements. According to Google\'s documentation, once Google detects an invalid element in the head, it assumes the end of the head element and stops reading any further elements. This can cause important meta tags to be missed.';

    public string $priority = 'high';

    public int $timeToFix = 2;

    public int $scoreWeight = 8;

    public bool $continueAfterFailure = true;

    public ?string $failureReason = null;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    /**
     * Valid HTML elements that are allowed in the head section
     * Based on HTML5 specification and Google's documentation
     */
    private array $validHeadElements = [
        'title',
        'base',
        'link',
        'meta',
        'style',
        'script',
        'noscript',
        'template',
    ];

    public function check(): void(): void(Response $response, Crawler $crawler): bool
    {
        return $this->validateContent($response);
    }

    public function validateContent(): void(): void(Response $response): bool
    {
        // Get the raw HTML content from the response
        $html = $response->body();

        // Extract the head section using regex
        if (preg_match('/<head[^>]*>(.*?)<\/head>/is', $html, $matches)) {
            $headContent = $matches[1];
        } else {
            // No head section found
            $this->failureReason = __('failed.meta.invalid_head_elements.no_head');
            $this->actualValue = 'No head section found';

            return false;
        }

        // Extract all HTML tags from the head content, but exclude tags inside template elements
        $headTags = [];

        // First, remove template content to avoid detecting nested elements
        $headContentWithoutTemplates = preg_replace('/<template[^>]*>.*?<\/template>/is', '', $headContent);

        // Extract tags from the cleaned content
        preg_match_all('/<([a-zA-Z][a-zA-Z0-9]*)[^>]*>/i', (string) $headContentWithoutTemplates, $matches);
        $headTags = $matches[1];

        if ($headTags === []) {
            // No elements in head section
            $this->failureReason = __('failed.meta.invalid_head_elements.no_head');
            $this->actualValue = 'No head elements found';

            return false;
        }

        $invalidElements = [];

        foreach ($headTags as $headTag) {
            $headTag = strtolower($headTag);

            // Check if the element is valid for the head section
            if (! in_array($headTag, $this->validHeadElements)) {
                $invalidElements[] = $headTag;
            }
        }

        if ($invalidElements !== []) {
            $this->failureReason = __('failed.meta.invalid_head_elements.found', [
                'actualValue' => implode(', ', array_unique($invalidElements)),
            ]);
            $this->actualValue = $invalidElements;

            return false;
        }

        return true;
    }
}
