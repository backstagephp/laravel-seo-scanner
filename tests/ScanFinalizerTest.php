<?php

use Backstage\Seo\Events\ScanCompleted;
use Backstage\Seo\Models\SeoScan as SeoScanModel;
use Backstage\Seo\Services\ScanFinalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    config(['seo.database.connection' => 'testing']);
    config(['seo.cache.driver' => 'array']);
    $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
});

it('finalizes the scan from persisted scores and fires ScanCompleted', function () {
    Event::fake();

    $scan = SeoScanModel::create(['total_checks' => 1, 'started_at' => now()->subSeconds(5)]);

    DB::connection('testing')->table('seo_scores')->insert([
        [
            'seo_scan_id' => $scan->id,
            'url' => 'https://example.com/a',
            'score' => 100,
            'checks' => json_encode(['failed' => [], 'successful' => ['Some\\PassingCheck' => ['title' => 'ok']]]),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'seo_scan_id' => $scan->id,
            'url' => 'https://example.com/b',
            'score' => 50,
            'checks' => json_encode([
                'failed' => ['Backstage\\Seo\\Checks\\Content\\MultipleHeadingCheck' => ['title' => 'fail']],
                'successful' => [],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    app(ScanFinalizer::class)->finalize($scan);

    $scan->refresh();

    expect($scan->pages)->toBe(2)
        ->and($scan->failed_checks)->toBe(['Backstage\\Seo\\Checks\\Content\\MultipleHeadingCheck'])
        ->and($scan->finished_at)->not->toBeNull();

    Event::assertDispatched(ScanCompleted::class);
});
