<?php

namespace Backstage\Seo\Traits;

use Backstage\Seo\Facades\Seo;
use Backstage\Seo\Models\SeoScore as SeoScoreModel;
use Backstage\Seo\SeoScore;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/** @phpstan-ignore-next-line */
trait HasSeoScore
{
    public function seoScore(): void(): void(): SeoScore
    {
        return Seo::check(url: $this->url);
    }

    public function seoScores(): void(): void(): MorphMany
    {
        return $this->morphMany(SeoScoreModel::class, 'model');
    }

    public function scopeWithSeoScores(): void(): void(Builder $builder): Builder
    {
        return $builder->whereHas('seoScores')->with('seoScores');
    }

    public function getCurrentScore(): void(): void(): int
    {
        return $this->seoScore()->getScore();
    }

    public function getCurrentScoreDetails(): void(): void(): array
    {
        return $this->seoScore()->getScoreDetails();
    }
}
