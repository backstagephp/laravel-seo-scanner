<?php

namespace Backstage\Seo\Checks\Content;

use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;
use Backstage\Seo\Traits\Translatable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class KeywordsCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = 'The page has the keywords from the content model sufficiently applied in the title and content';

    public string $description = 'The keywords from the content model should be sufficiently applied in the title and content of the page to improve SEO performance.';

    public string $priority = 'high';

    public int $timeToFix = 15;

    public int $scoreWeight = 8;

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
        // Get keywords from the model (if available)
        $modelKeywords = $this->getModelKeywords();

        // Get meta keywords
        $metaKeywords = $this->getMetaKeywords($crawler);

        // Determine which keywords to use based on configuration
        $prioritizeModel = config('seo.keywords_check.prioritize_model_keywords', true);
        
        if ($prioritizeModel && !empty($modelKeywords)) {
            $keywords = $modelKeywords;
        } elseif (!empty($metaKeywords)) {
            $keywords = $metaKeywords;
        } else {
            $keywords = $modelKeywords; // Use model keywords even if empty
        }

        if (empty($keywords)) {
            $this->failureReason = __('failed.content.keywords_check.no_keywords');
            $this->actualValue = 'No keywords found';
            return false;
        }

        $this->expectedValue = $keywords;

        // Get title and content
        $title = $this->getTitle($crawler);
        $content = $this->getContent($crawler);

        if (empty($title) && empty($content)) {
            $this->failureReason = __('failed.content.keywords_check.no_content');
            $this->actualValue = 'No title or content found';
            return false;
        }

        // Check keyword usage
        $keywordUsage = $this->analyzeKeywordUsage($keywords, $title, $content);

        $minimumScore = config('seo.keywords_check.minimum_score', 60);

        if ($keywordUsage['score'] < $minimumScore) {
            $this->failureReason = __('failed.content.keywords_check.insufficient_usage', [
                'actualValue' => $keywordUsage['score'] . '%',
                'expectedValue' => $minimumScore . '%',
                'missingKeywords' => implode(', ', $keywordUsage['missing_keywords'])
            ]);
            $this->actualValue = $keywordUsage;
            return false;
        }

        return true;
    }

    private function getModelKeywords(): array
    {
        // Try to get keywords from the current model context if available
        if ($this->model) {
            $attributeName = config('seo.keywords_check.model_keywords_attribute', 'keywords');
            
            // Get keywords from the configured attribute name
            $raw = $this->model->{$attributeName} ?? null;

            if (is_string($raw)) {
                return array_values(array_filter(array_map('trim', explode(',', $raw))));
            }

            if (is_array($raw)) {
                return array_values(array_filter(array_map('trim', $raw)));
            }
        }

        return [];
    }

    private function getMetaKeywords(Crawler $crawler): array
    {
        $node = $crawler->filterXPath('//meta[@name="keywords"]')->getNode(0);

        if (! $node) {
            return [];
        }

        $keywords = $crawler->filterXPath('//meta[@name="keywords"]')->attr('content');

        if (! $keywords) {
            return [];
        }

        return array_map('trim', explode(',', $keywords));
    }

    private function getTitle(Crawler $crawler): string
    {
        $node = $crawler->filterXPath('//title')->getNode(0);

        if (! $node) {
            return '';
        }

        return $crawler->filterXPath('//title')->text();
    }

    private function getContent(Crawler $crawler): string
    {
        $node = $crawler->filterXPath('//body')->getNode(0);

        if (! $node) {
            return '';
        }

        return $crawler->filterXPath('//body')->text();
    }

    private function analyzeKeywordUsage(array $keywords, string $title, string $content): array
    {
        $titleLower = strtolower($title);
        $contentLower = strtolower($content);
        $totalKeywords = count($keywords);
        $foundKeywords = [];
        $missingKeywords = [];
        $titleScore = 0;
        $contentScore = 0;

        $useWordBoundaries = config('seo.keywords_check.use_word_boundaries', true);

        foreach ($keywords as $keyword) {
            $keywordLower = strtolower(trim($keyword));
            
            if ($useWordBoundaries) {
                // Check if keyword is in title (using word boundaries)
                $inTitle = preg_match('/\b' . preg_quote($keywordLower, '/') . '\b/', $titleLower);
                
                // Check if keyword is in content (using word boundaries)
                $inContent = preg_match('/\b' . preg_quote($keywordLower, '/') . '\b/', $contentLower);
            } else {
                // Simple substring matching
                $inTitle = str_contains($titleLower, $keywordLower);
                $inContent = str_contains($contentLower, $keywordLower);
            }
            
            if ($inTitle || $inContent) {
                $foundKeywords[] = $keyword;
                
                if ($inTitle) {
                    $titleScore += 1;
                }
                
                if ($inContent) {
                    $contentScore += 1;
                }
            } else {
                $missingKeywords[] = $keyword;
            }
        }

        // Calculate overall score using configurable weights
        $titleWeight = config('seo.keywords_check.title_weight', 0.4);
        $contentWeight = config('seo.keywords_check.content_weight', 0.6);
        
        $titlePercentage = $totalKeywords > 0 ? ($titleScore / $totalKeywords) * 100 : 0;
        $contentPercentage = $totalKeywords > 0 ? ($contentScore / $totalKeywords) * 100 : 0;
        
        $overallScore = ($titlePercentage * $titleWeight) + ($contentPercentage * $contentWeight);

        return [
            'score' => round($overallScore, 1),
            'title_score' => round($titlePercentage, 1),
            'content_score' => round($contentPercentage, 1),
            'found_keywords' => $foundKeywords,
            'missing_keywords' => $missingKeywords,
            'total_keywords' => $totalKeywords,
            'found_count' => count($foundKeywords),
            'missing_count' => count($missingKeywords)
        ];
    }
}
