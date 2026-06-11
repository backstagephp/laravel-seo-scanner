<?php

namespace Backstage\Seo;

use Illuminate\Support\Collection;

class SeoScore
{
    public int|float $score = 0;

    public Collection $successful;

    public Collection $failed;

    public function __invoke(Collection $successful, Collection $failed): self
    {
        $this->successful = $successful;
        $this->failed = $failed;

        if (! $successful->count()) {
            $this->score = 0;

            return $this;
        }

        $successfulScoreWeight = $successful->sum('scoreWeight');
        $failedScoreWeight = $failed->sum('scoreWeight');
        $totalScoreWeight = $successfulScoreWeight + $failedScoreWeight;

        $this->score = round($successfulScoreWeight / $totalScoreWeight * 100);

        return $this;
    }

    public function getScore(): int|float
    {
        return $this->score;
    }

    public function getScoreDetails(): array
    {
        return [
            'score' => $this->score,
            'successful' => $this->successful,
            'failed' => $this->failed,
        ];
    }

    public function getFailedChecks(): Collection
    {
        return $this->failed;
    }

    public function getSuccessfulChecks(): Collection
    {
        return $this->successful;
    }

    public function getAllChecks(): Collection
    {
        return collect(['successful' => $this->successful])->merge(['failed' => $this->failed]);
    }

    /**
     * Return a machine-readable representation of the score and its checks,
     * suitable for JSON output.
     */
    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'passed' => $this->successful->count(),
            'failed' => $this->failed->count(),
            'checks' => [
                'passed' => $this->successful
                    ->map(fn ($check, $class) => $this->mapCheck($check, $class, false))
                    ->values()
                    ->all(),
                'failed' => $this->failed
                    ->map(fn ($check, $class) => $this->mapCheck($check, $class, true))
                    ->values()
                    ->all(),
            ],
        ];
    }

    private function mapCheck(object $check, string $class, bool $failed): array
    {
        $segments = explode('\\', $class);

        $data = [
            'check' => $class,
            'category' => $segments[count($segments) - 2] ?? null,
            'title' => $check->title,
            'priority' => $check->priority,
            'scoreWeight' => $check->scoreWeight,
        ];

        if ($failed) {
            $data['timeToFix'] = $check->timeToFix;
            $data['failureReason'] = $check->failureReason ?? null;
            $data['actualValue'] = $check->actualValue ?? null;
            $data['expectedValue'] = $check->expectedValue ?? null;
        }

        return $data;
    }
}
