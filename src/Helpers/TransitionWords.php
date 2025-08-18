<?php

namespace Backstage\Seo\Helpers;

class TransitionWords
{
    public static array $transitionWords = [
        'addition' => [
            'additionally', 'moreover', 'furthermore', 'in addition',
            'not only', 'but also', 'as well as', 'besides', "what's more",
        ],
        'contrast' => [
            'however', 'nevertheless', 'on the other hand', 'in contrast',
            'conversely', 'although', 'while', 'yet', 'even though', 'nonetheless',
        ],
        'comparison' => [
            'similarly', 'likewise', 'in comparison', 'just as',
            'compared to', 'similarly to',
        ],
        'cause_and_effect' => [
            'therefore', 'thus', 'consequently', 'as a result', 'because',
            'since', 'so', 'due to', 'owing to', 'accordingly',
        ],
        'emphasis' => [
            'indeed', 'certainly', 'of course', 'undoubtedly',
            'without a doubt', 'naturally',
        ],
        'example' => [
            'for example', 'for instance', 'such as', 'to illustrate', 'in particular',
        ],
        'sequence_order' => [
            'first', 'second', 'third', 'next', 'then', 'afterward', 'meanwhile',
            'finally', 'in the meantime', 'subsequently',
        ],
        'conclusion_summary' => [
            'in conclusion', 'to sum up', 'ultimately', 'in summary', 'all in all', 'overall',
        ],
        'time' => [
            'meanwhile', 'before', 'after', 'during', 'while', 'since', 'until', 'eventually', 'soon', 'in the past', 'in the future',
        ],
        'clarification' => [
            'in other words', 'that is to say', 'specifically', 'to clarify',
        ],
        'illustration' => [
            'specifically', 'in this case', 'an example of this is', 'to demonstrate',
        ],
        'concession' => [
            'admittedly', 'granted', 'even though', 'while it is true',
        ],
    ];

    public static function getTransitionWords(): array
    {
        return self::$transitionWords;
    }

    public static function getTransitionWordsOnly(?string $locale = null): array
    {
        $transitionWords = self::$transitionWords;

        $words = [];

        foreach ($transitionWords as $transitionWord) {
            foreach ($transitionWord as $word) {
                $words[] = __($word, [], $locale);
            }
        }

        return $words;
    }

    public static function getTransitionWordsByType(string $type): array
    {
        return self::$transitionWords[$type];
    }
}
