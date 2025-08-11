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
    public function seoScore(): SeoScore
    {
        return Seo::check(url: $this->url);
    }

    public function seoScores(): MorphMany
    {
        return $this->morphMany(SeoScoreModel::class, 'model');
    }

    public function scopeWithSeoScores(Builder $query): Builder
    {
        return $query->whereHas('seoScores')->with('seoScores');
    }

    public function getCurrentScore(): int
    {
        return $this->seoScore()->getScore();
    }

    public function getCurrentScoreDetails(): array
    {
        return $this->seoScore()->getScoreDetails();
    }
}
