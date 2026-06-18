<?php

namespace Backstage\Seo\Jobs;

use Backstage\Seo\Models\SeoScan as SeoScanModel;
use Backstage\Seo\Services\PageScanRunner;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;

class ScanChunk implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    /**
     * Fail the job only after this many uncaught exceptions. Rate-limit
     * releases are not exceptions, so they don't consume this budget — this
     * just stops a genuinely broken chunk from retrying until retryUntil().
     */
    public $maxExceptions = 3;

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

    /**
     * Bound retries by time rather than by a hard attempt count.
     *
     * The RateLimited middleware throttles by releasing the job back onto the
     * queue, and each release counts as an attempt — so a hard `$tries` cap
     * would fail every throttled chunk on its next pickup instead of resuming
     * it. A time bound lets the job ride out throttle waves while still
     * guaranteeing it can't loop forever. When throttling is off we return
     * null so the worker's own --tries governs as usual.
     */
    public function retryUntil(): ?\DateTimeInterface
    {
        if (! config('seo.throttle.enabled')) {
            return null;
        }

        $hours = (int) config('seo.throttle.retry_until_hours', 24);

        return $hours > 0 ? now()->addHours($hours) : null;
    }

    public function handle(PageScanRunner $runner): void
    {
        $scan = SeoScanModel::find($this->scanId);

        if (! $scan) {
            return;
        }

        foreach ($this->urls as $url) {
            $this->scanSafely($runner, $scan, $url);
        }

        if ($this->model && ! empty($this->ids)) {
            $instance = new $this->model;

            $instance->newQuery()
                ->whereIn($instance->getKeyName(), $this->ids)
                ->get()
                ->filter->url
                ->each(fn ($model) => $this->scanSafely($runner, $scan, $model->url, $model));
        }
    }

    /**
     * Scan a single page, isolating failures so one unscannable page can't
     * abort the whole chunk and lose every other page in it.
     */
    private function scanSafely(PageScanRunner $runner, SeoScanModel $scan, string $url, ?Model $model = null): void
    {
        try {
            $runner->scan($scan, $url, $model, useJavascript: config('seo.javascript'));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
