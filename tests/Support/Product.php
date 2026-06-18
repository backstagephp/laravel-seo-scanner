<?php

namespace Backstage\Seo\Tests\Support;

use Backstage\Seo\SeoInterface;
use Backstage\Seo\Traits\HasSeoScore;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements SeoInterface
{
    use HasSeoScore;

    protected $table = 'products';

    protected $guarded = [];

    public $timestamps = false;

    public function getUrlAttribute(): ?string
    {
        return $this->attributes['url'] ?? null;
    }
}
