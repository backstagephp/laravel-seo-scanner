<?php

namespace Backstage\Seo\Services;

use Backstage\Seo\Facades\Seo;
use Backstage\Seo\Models\SeoScan as SeoScanModel;
use Backstage\Seo\SeoScore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;

class PageScanRunner
{
    /**
     * Scan a single page (URL or model instance), persist its score when
     * database saving is enabled, and return the resulting SeoScore.
     */
    public function scan(
        ?SeoScanModel $scan,
        string $url,
        ?Model $model = null,
        bool $useJavascript = false,
        ?ProgressBar $progress = null,
    ): SeoScore {
        $seo = Seo::check(url: $url, progress: $progress, useJavascript: $useJavascript);

        if (config('seo.database.save')) {
            $this->persist($scan, $seo, $url, $model);
        }

        return $seo;
    }

    private function persist(SeoScanModel $scan, SeoScore $seo, string $url, ?Model $model): void
    {
        DB::connection(config('seo.database.connection'))
            ->table('seo_scores')
            ->insert([
                'seo_scan_id' => $scan->id,
                'url' => $url,
                'model_type' => $model?->getMorphClass(),
                'model_id' => $model?->getKey(),
                'score' => $seo->getScore(),
                'checks' => json_encode([
                    'failed' => $seo->getFailedChecks(),
                    'successful' => $seo->getSuccessfulChecks(),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
