<?php

use Backstage\Seo\Checks\Content\MultipleHeadingCheck;
use Backstage\Seo\Tests\Support\Product;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['seo.database.connection' => 'testing']);
    config(['seo.database.save' => true]);
    config(['seo.checks' => [MultipleHeadingCheck::class]]);
    config(['seo.check_routes' => false]);
    config(['seo.models' => [Product::class]]);
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

    Schema::create('products', function ($table) {
        $table->bigIncrements('id');
        $table->string('url')->nullable();
    });

    Product::create(['url' => 'https://example.com/product/1']);
});

afterEach(function () {
    Schema::dropIfExists('products');
});

it('dispatches the scan batch onto the configured queue', function () {
    config(['seo.queue' => 'seo']);

    Bus::fake();

    $this->artisan('seo:scan --queue')->assertExitCode(0);

    Bus::assertBatched(fn (PendingBatch $batch) => $batch->queue() === 'seo');
});

it('uses the default queue when no queue is configured', function () {
    config(['seo.queue' => null]);

    Bus::fake();

    $this->artisan('seo:scan --queue')->assertExitCode(0);

    Bus::assertBatched(fn (PendingBatch $batch) => $batch->queue() === null);
});
