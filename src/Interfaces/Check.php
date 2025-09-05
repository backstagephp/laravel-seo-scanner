<?php

namespace Backstage\Seo\Interfaces;

use Closure;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @property string $title
 * @property string $description
 * @property string $priority
 * @property int $timeToFix
 * @property int $scoreWeight
 * @property bool $continueAfterFailure
 * @property string $failureReason
 * @property mixed $actualValue
 * @property int|null $expectedValue
 *
 * @method check()
 * @method __invoke()
 * @method setResult()
 */
interface Check
{
    public function check(): void(Response $response, Crawler $crawler): bool;

    public function __invoke(): void(array $data, Closure $next);

    public function setResult(): void(array $data, bool $result): array;

    public function getTranslatedDescription(): void(): string;
}
