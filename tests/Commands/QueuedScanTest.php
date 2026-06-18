<?php

use Backstage\Seo\Checks\Content\MultipleHeadingCheck;
use Backstage\Seo\Jobs\ScanChunk;
use Backstage\Seo\Models\SeoScan as SeoScanModel;
use Backstage\Seo\Tests\Support\Product;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['seo.database.connection' => 'testing']);
    config(['seo.database.save' => true]);
    config(['seo.checks' => [MultipleHeadingCheck::class]]);
    config(['seo.check_routes' => false]);
    config(['seo.models' => [Product::class]]);
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

    // The non-queued run scans inline, so stub HTTP to keep it off the network.
    Http::fake([
        '*' => Http::response('<html><head><title>Test</title></head><body><h1>Test</h1></body></html>'),
    ]);

    Schema::create('products', function ($table) {
        $table->bigIncrements('id');
        $table->string('url')->nullable();
    });
});

afterEach(function () {
    Schema::dropIfExists('products');
});

it('dispatches a batch of chunk jobs sized by chunk_size', function () {
    foreach (range(1, 5) as $i) {
        Product::create(['url' => "https://example.com/product/{$i}"]);
    }

    config(['seo.chunk_size' => 2]);

    Bus::fake();

    $this->artisan('seo:scan --queue')->assertExitCode(0);

    // 5 records in chunks of 2 => 3 jobs.
    Bus::assertBatched(function (PendingBatch $batch) {
        return $batch->jobs->count() === 3
            && $batch->jobs->every(fn ($job) => $job instanceof ScanChunk);
    });
});

it('creates a scan record when dispatching to the queue', function () {
    Product::create(['url' => 'https://example.com/product/1']);

    Bus::fake();

    $this->artisan('seo:scan --queue')->assertExitCode(0);

    expect(SeoScanModel::on('testing')->count())->toBe(1);
});

it('runs synchronously without the queue flag', function () {
    Product::create(['url' => 'https://example.com/product/1']);

    Bus::fake();

    $this->artisan('seo:scan')->assertExitCode(0);

    Bus::assertNothingBatched();
});
