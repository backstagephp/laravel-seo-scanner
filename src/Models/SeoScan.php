<?php

namespace Backstage\Seo\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $pages
 * @property int $total_checks
 * @property array $failed_checks
 * @property float $time
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $started_at
 * @property Carbon $finished_at
 */
class SeoScan extends Model
{
    use Prunable;

    protected $guarded = [];

    protected $casts = [
        'failed_checks' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('seo.database.connection'));
        }

        $this->setTable('seo_scans');

        parent::__construct($attributes);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(SeoScore::class);
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(config('seo.database.prune.older_than_days')));
    }
}
