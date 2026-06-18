<?php

use Backstage\Seo\Checks\Content\MultipleHeadingCheck;
use Backstage\Seo\Tests\Support\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('products', function ($table) {
        $table->bigIncrements('id');
        $table->string('url')->nullable();
    });

    Http::fake([
        '*' => Http::response('<html><head><title>Test</title></head><body><h1>Test</h1></body></html>'),
    ]);

    config(['seo.database.save' => false]);
    config(['seo.check_routes' => false]);
    config(['seo.checks' => [MultipleHeadingCheck::class]]);
    config(['seo.models' => [Product::class]]);
});

afterEach(function () {
    Schema::dropIfExists('products');
});

it('scans every model record that has a url and skips the rest', function () {
    foreach (range(1, 5) as $i) {
        Product::create(['url' => "https://example.com/product/{$i}"]);
    }

    // Records without a url must be skipped.
    Product::create(['url' => null]);
    Product::create(['url' => null]);

    $this->artisan('seo:scan')
        ->expectsOutputToContain('on 5 pages')
        ->assertExitCode(0);
});

it('reads model records in chunks instead of loading them all at once', function () {
    foreach (range(1, 5) as $i) {
        Product::create(['url' => "https://example.com/product/{$i}"]);
    }

    config(['seo.chunk_size' => 2]);

    DB::connection()->enableQueryLog();

    $this->artisan('seo:scan')->assertExitCode(0);

    $productSelects = collect(DB::connection()->getQueryLog())
        ->filter(fn ($query) => str_contains($query['query'], 'from "products"')
            && str_starts_with(trim($query['query']), 'select'));

    // With chunked reads (chunk size 2 over 5 records) the table is queried
    // in several batches, not in a single unbounded select.
    expect($productSelects->count())->toBeGreaterThan(1);
});
