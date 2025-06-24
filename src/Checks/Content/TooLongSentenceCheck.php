<?php

namespace Backstage\Seo\Checks\Content;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\Actions;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

class TooLongSentenceCheck implements Check
{
    use Actions,
        PerformCheck,
        Translatable;

    public string $title = 'Too long sentence check';

    public string $description = 'The content should not contain sentences with more than 20 words.';

    public string $priority = 'medium';

    public int $timeToFix = 30;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = true;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(Response $response, Crawler $crawler): bool
    {
        if ($this->validateContent($response, $crawler)) {
            return true;
        }

        return false;
    }

    public function validateContent(Response $response, Crawler $crawler): bool
    {
        $phrases = $this->extractPhrases(
            $this->getTextContent($response, $crawler)
        );

        $sentencesWithTooManyWords = $this->calculateSentencesWithTooManyWords($phrases);
        $this->actualValue = $sentencesWithTooManyWords;

        if (count($sentencesWithTooManyWords) === 0) {
            return true;
        }

        // If more than 20% of the total sentences are too long, fail
        if (count($sentencesWithTooManyWords) / count($phrases) > 0.2) {

            // Count how many sentences needed to fix to fall below 20%
            $sentencesNeededToFix = count($sentencesWithTooManyWords) - (count($phrases) * 0.2);

            if ($sentencesNeededToFix < 1) {
                $sentencesNeededToFix = 1;
            }

            $this->failureReason = __('failed.content.too_long_sentence', [
                'actualValue' => count($this->actualValue),
                'neededToFix' => round($sentencesNeededToFix, 0, PHP_ROUND_HALF_UP),
            ]);

            return false;
        }

        return true;
    }

    private function calculateSentencesWithTooManyWords(array $sentences): array
    {
        $tooLongSentences = [];

        foreach ($sentences as $sentence) {
            if (str_word_count($sentence) > 20) {
                $tooLongSentences[] = $sentence;
            }
        }

        return $tooLongSentences;
    }
}
