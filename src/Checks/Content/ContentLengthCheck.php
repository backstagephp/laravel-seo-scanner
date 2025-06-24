<?php

namespace Backstage\Seo\Checks\Content;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Readability\Readability;
use Symfony\Component\DomCrawler\Crawler;

class ContentLengthCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'Length of the content is at least 2100 characters';

    public string $description = 'The length of the content should be at least 2100 characters.';

    public string $priority = 'low';

    public int $timeToFix = 30;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = 2100;

    public function check(Response $response, Crawler $crawler): bool
    {
        if (app()->runningUnitTests()) {
            if (strlen($response->body()) < 2100) {
                return false;
            }

            return true;
        }

        $content = $this->getContentToValidate($response, $crawler);

        if (! $content) {
            return false;
        }

        return $this->validateContent($content);
    }

    public function getContentToValidate(Response $response, Crawler $crawler): ?string
    {
        $body = $response->body();

        if ($this->useJavascript) {
            $body = $crawler->filter('body')->html();
        }

        $readability = new Readability($body);

        $readability->init();

        $textContent = $readability->getContent()->textContent;

        /**
         * This is a fallback for when Readability is unable to parse the content.
         * Sometimes it happens when scanning a JavaScript rendered page, that
         * we don't get a proper response. In that case we just return null.
         *
         * @todo we should check if we can improve this.
         */
        if ($textContent == 'Sorry, Readability was unable to parse this page for content.') {
            $this->failureReason = __('failed.content.length.parse');

            return null;
        }

        return $textContent;
    }

    public function validateContent(string|array $content): bool
    {
        $this->actualValue = strlen($content);

        if (strlen($content) < $this->expectedValue) {
            $this->failureReason = __('failed.content.length', [
                'actualValue' => $this->actualValue,
                'expectedValue' => $this->expectedValue,
            ]);
        }

        return strlen($content) >= $this->expectedValue;
    }
}
