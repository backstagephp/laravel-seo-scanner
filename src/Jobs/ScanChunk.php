<?php

namespace Backstage\Seo\Jobs;

use Backstage\Seo\Models\SeoScan as SeoScanModel;
use Backstage\Seo\Services\PageScanRunner;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;

class ScanChunk implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $timeout = 600;

    /**
     * @param  array<int, string>  $urls  Route URLs to scan.
     * @param  class-string|null  $model  Model class to scan records of.
     * @param  array<int, int|string>  $ids  Primary keys of the model records.
     */
    public function __construct(
        public int $scanId,
        public array $urls = [],
        public ?string $model = null,
        public array $ids = [],
    ) {}

    /**
     * Throttle chunk jobs across all workers when throttling is enabled, so
     * parallel workers collectively respect the configured request rate.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return config('seo.throttle.enabled')
            ? [new RateLimited('seo-scan')]
            : [];
    }

    public function handle(PageScanRunner $runner): void
    {
        $scan = SeoScanModel::find($this->scanId);

        if (! $scan) {
            return;
        }

        foreach ($this->urls as $url) {
            $runner->scan($scan, $url, useJavascript: config('seo.javascript'));
        }

        if ($this->model && ! empty($this->ids)) {
            $instance = new $this->model;

            $instance->newQuery()
                ->whereIn($instance->getKeyName(), $this->ids)
                ->get()
                ->filter->url
                ->each(fn ($model) => $runner->scan($scan, $model->url, $model));
        }
    }
}
