<?php

namespace Backstage\Seo\Commands;

use Backstage\Seo\Events\ScanCompleted;
use Backstage\Seo\Jobs\ScanChunk;
use Backstage\Seo\Models\SeoScan as SeoScanModel;
use Backstage\Seo\SeoScore;
use Backstage\Seo\Services\PageScanRunner;
use Backstage\Seo\Services\ScanFinalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\Console\Helper\ProgressBar;

class SeoScan extends Command
{
    public $signature = 'seo:scan {--format=console : The output format (console or json)} {--queue : Dispatch the scan as batched queue jobs instead of running it synchronously}';

    public $description = 'Scan the SEO score of your website';

    public int $success = 0;

    public int $failed = 0;

    public int $modelCount = 0;

    public int $routeCount = 0;

    public ?ProgressBar $progress = null;

    public bool $json = false;

    public array $results = [];

    public array $failedChecks = [];

    public ?SeoScanModel $scan = null;

    public function handle(): int
    {
        $this->json = $this->option('format') === 'json';

        if (empty(config('seo.models')) && ! config('seo.check_routes')) {
            $this->error('No models or routes specified in config/seo.php');

            return self::FAILURE;
        }

        if ($this->option('queue')) {
            return $this->dispatchQueuedScan();
        }

        if (config('seo.database.save')) {
            $scan = SeoScanModel::create([
                'total_checks' => getCheckCount(),
                'started_at' => now(),
            ]);

            $this->scan = $scan;
        }

        $startTime = microtime(true);

        if (! $this->json) {
            $this->info('Please wait while we scan your web page(s)...');
            $this->line('');

            $this->progress = $this->output->createProgressBar(getCheckCount());
            $this->line('');
        }

        if (config('seo.check_routes')) {
            $this->calculateScoreForRoutes();
        }

        if (config('seo.models')) {
            foreach (config('seo.models') as $model) {
                if (is_array($model)) {
                    $this->calculateScoreForModel($model[0], $model[1]);
                } else {
                    $this->calculateScoreForModel($model);
                }
            }
        }

        $totalPages = $this->modelCount + $this->routeCount;

        if ($this->json) {
            $this->output->writeln(json_encode($this->results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            $this->info('Command completed with '.$this->failed.' failed and '.$this->success.' successful checks on '.$totalPages.' pages.');
        }

        cache()->driver(config('seo.cache.driver'))->tags('seo')->flush();

        if (config('seo.database.save')) {
            $scan->update([
                'pages' => $totalPages,
                'failed_checks' => $this->failedChecks,
                'time' => microtime(true) - $startTime,
                'finished_at' => now(),
            ]);
        }

        event(ScanCompleted::class);

        return self::SUCCESS;
    }

    private function dispatchQueuedScan(): int
    {
        $scan = SeoScanModel::create([
            'total_checks' => getCheckCount(),
            'started_at' => now(),
        ]);

        $jobs = $this->buildChunkJobs($scan);

        if (empty($jobs)) {
            $this->error('No pages found to scan.');

            return self::FAILURE;
        }

        $scanId = $scan->id;

        Bus::batch($jobs)
            ->name('SEO scan #'.$scanId)
            ->finally(function () use ($scanId) {
                if ($scan = SeoScanModel::find($scanId)) {
                    app(ScanFinalizer::class)->finalize($scan);
                }
            })
            ->dispatch();

        $pages = collect($jobs)->sum(fn (ScanChunk $job) => count($job->urls) + count($job->ids));

        $this->info('Dispatched '.count($jobs).' scan job(s) to the queue for '.$pages.' page(s).');

        return self::SUCCESS;
    }

    /**
     * Build the batch of ScanChunk jobs for the configured routes and models.
     *
     * @return array<int, ScanChunk>
     */
    private function buildChunkJobs(SeoScanModel $scan): array
    {
        $chunkSize = (int) config('seo.chunk_size', 100);
        $jobs = [];

        if (config('seo.check_routes')) {
            self::getRoutes()
                ->map(fn ($uri, $name) => route($name))
                ->values()
                ->chunk($chunkSize)
                ->each(function (Collection $urls) use (&$jobs, $scan) {
                    $jobs[] = new ScanChunk(scanId: $scan->id, urls: $urls->all());
                });
        }

        foreach (config('seo.models') ?? [] as $model) {
            [$class, $scope] = is_array($model) ? [$model[0], $model[1]] : [$model, null];

            $query = new $class;

            if ($scope) {
                $query = $query->{$scope}();
            }

            $query->lazyById($chunkSize)
                ->filter->url
                ->chunk($chunkSize)
                ->each(function ($items) use (&$jobs, $scan, $class) {
                    $jobs[] = new ScanChunk(
                        scanId: $scan->id,
                        model: $class,
                        ids: collect($items)->map->getKey()->values()->all(),
                    );
                });
        }

        return $jobs;
    }

    private function calculateScoreForRoutes(): void
    {
        $routes = self::getRoutes();
        $throttleEnabled = config('seo.throttle.enabled');
        $maxRequests = config('seo.throttle.requests_per_minute') ?? 'N/A';
        $requestCount = 0;
        $startTime = time();

        if ($throttleEnabled) {
            if (! $this->json) {
                $this->line('<fg=yellow>Throttling enabled. Maximum requests per minute: '.$maxRequests.'</>');
            }
            sleep(5);
        }

        $routes->each(function ($path, $name) use ($throttleEnabled, $maxRequests, &$requestCount, &$startTime) {
            $this->progress?->start();

            if ($throttleEnabled) {

                if ($requestCount >= $maxRequests) {
                    $elapsedTime = time() - $startTime;
                    if ($elapsedTime < 60) {
                        sleep(60 - $elapsedTime);
                    }
                    $requestCount = 0;
                    $startTime = time();
                }
                $requestCount++;
            }

            $this->performSeoCheck($name);
            $this->progress?->finish();
        });
    }

    private function performSeoCheck($name): void
    {
        $url = route($name);

        $seo = app(PageScanRunner::class)->scan(
            scan: $this->scan,
            url: $url,
            progress: $this->progress,
            useJavascript: config('seo.javascript'),
        );

        $this->accumulate($seo);
        $this->routeCount++;

        $this->recordResult($seo, $url);
    }

    private function accumulate(SeoScore $seo): void
    {
        $this->failed += count($seo->getFailedChecks());
        $this->success += count($seo->getSuccessfulChecks());

        $this->failedChecks = array_values(array_unique(array_merge(
            $this->failedChecks,
            $seo->getFailedChecks()->map(fn ($check) => get_class($check))->values()->all(),
        )));
    }

    private function recordResult(SeoScore $seo, string $url): void
    {
        if ($this->json) {
            $this->results[] = array_merge(['url' => $url], $seo->toArray());

            return;
        }

        $this->logResultToConsole($seo, $url);
    }

    private static function getRoutes(): Collection
    {
        $routes = collect(app('router')->getRoutes()->getRoutesByName())
            ->filter(fn ($route) => $route->methods[0] === 'GET');

        // Check if all routes should be checked
        if (in_array('*', Arr::wrap(config('seo.routes')))) {
            $routes = $routes->map(fn ($route) => $route->uri);
        } else {
            // Only check the routes specified in the config
            $routes = $routes->filter(fn ($route) => in_array($route->getName(), Arr::wrap(config('seo.routes'))))
                ->map(fn ($route) => $route->uri);
        }

        // Filter out excluded routes by name
        if (! empty(config('seo.exclude_routes'))) {
            $routes = $routes->filter(fn ($route, $name) => ! in_array($name, config('seo.exclude_routes')));
        }

        // Filter out excluded routes by path
        if (! empty(config('seo.exclude_paths'))) {
            $routes = $routes->filter(function ($route) {
                foreach (config('seo.exclude_paths') as $path) {
                    // if path contains a wildcard, check if the route starts with the path
                    if (str_contains($path, '*')) {
                        $path = str_replace('/*', '', $path);

                        if (str_starts_with($route, $path)) {
                            return false;
                        }
                    }

                    // if path does not contain a wildcard, check if the route contains the path
                    if (str_contains($route, $path)) {
                        return false;
                    }
                }

                return true;
            });
        }

        // Exclude routes that contain a parameter or where it ends with .txt or .xml
        $routes = $routes->filter(fn ($route) => ! str_contains($route, '{') &&
            ! str_ends_with($route, '.txt') &&
            ! str_ends_with($route, '.xml')
        );

        return $routes;
    }

    private function calculateScoreForModel(string $model, ?string $scope = null): void
    {
        $items = new $model;

        if ($scope) {
            $items = $items->{$scope}();
        }

        $items->get()->filter->url->map(function ($model) {
            $this->progress?->start();

            $seo = app(PageScanRunner::class)->scan(
                scan: $this->scan,
                url: $model->url,
                model: $model,
            );

            $this->accumulate($seo);
            $this->modelCount++;

            $this->progress?->finish();

            if ($this->failed === 0 && $this->success === 0) {
                if (! $this->json) {
                    $this->line('<fg=red>✘ Unfortunately, the url that is used is not correct. Please try again with a different url.</>');
                }

                return self::FAILURE;
            }

            $this->recordResult($seo, $model->url);
        });
    }

    private function logResultToConsole(SeoScore $seo, string $url): void
    {
        $this->line('');
        $this->line('');
        $this->line('-----------------------------------------------------------------------------------------------------------------------------------');
        $this->line('> '.$url.' | <fg=green>'.$seo->getSuccessfulChecks()->count().' passed</> <fg=red>'.($seo->getFailedChecks()->count().' failed</>'));
        $this->line('-----------------------------------------------------------------------------------------------------------------------------------');
        $this->line('');

        $seo->getAllChecks()->each(function ($checks, $type) {
            $checks->each(function ($check) use ($type) {
                if ($type == 'failed') {
                    $this->line('<fg=red>✘ '.$check->title.' failed.</>');

                    if (property_exists($check, 'failureReason')) {
                        $this->line($check->failureReason.' Estimated time to fix: '.$check->timeToFix.' minute(s).');

                        $this->line('');
                    }
                } else {
                    $this->line('<fg=green>✔ '.$check->title.'</>');
                }
            });

            $this->line('');
        });
    }
}
