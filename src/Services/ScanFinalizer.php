<?php

namespace Backstage\Seo\Services;

use Backstage\Seo\Events\ScanCompleted;
use Backstage\Seo\Models\SeoScan as SeoScanModel;
use Illuminate\Support\Facades\DB;

class ScanFinalizer
{
    /**
     * Finalize a scan once all its chunk jobs have run: derive the totals from
     * the persisted scores, update the scan record, flush the cache and fire
     * the ScanCompleted event. Safe to call regardless of how many workers
     * processed the chunks.
     */
    public function finalize(SeoScanModel $scan): void
    {
        $rows = DB::connection(config('seo.database.connection'))
            ->table('seo_scores')
            ->where('seo_scan_id', $scan->id)
            ->get(['checks']);

        $failedChecks = $rows->flatMap(function ($row) {
            $checks = json_decode($row->checks, true);

            return array_keys($checks['failed'] ?? []);
        })->unique()->values()->all();

        $scan->update([
            'pages' => $rows->count(),
            'failed_checks' => $failedChecks,
            'time' => $scan->started_at ? $scan->started_at->diffInSeconds(now(), true) : null,
            'finished_at' => now(),
        ]);

        cache()->driver(config('seo.cache.driver'))->tags('seo')->flush();

        event(new ScanCompleted);
    }
}
