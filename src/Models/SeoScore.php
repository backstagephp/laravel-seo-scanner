<?php

namespace Backstage\Seo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $seo_scan_id
 * @property string $url
 * @property string $model_type
 * @property int $model_id
 * @property int $score
 * @property array $checks
 */
class SeoScore extends Model
{
    use Prunable;

    protected $guarded = [];

    protected $casts = [
        'checks' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('seo.database.connection'));
        }

        $this->setTable('seo_scores');

        parent::__construct($attributes);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(SeoScan::class);
    }
}
