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
}
