<?php

use Backstage\Seo\Checks\Content\MultipleHeadingCheck;
use Backstage\Seo\Models\SeoScan as SeoScanModel;
use Backstage\Seo\Services\PageScanRunner;
use Backstage\Seo\SeoScore;
use Backstage\Seo\Tests\Support\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['seo.database.connection' => 'testing']);
    $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

    Schema::create('products', function ($table) {
        $table->bigIncrements('id');
        $table->string('url')->nullable();
    });

    Http::fake([
        '*' => Http::response('<html><head><title>Test</title></head><body><h1>Test</h1></body></html>'),
    ]);

    config(['seo.checks' => [MultipleHeadingCheck::class]]);
    config(['seo.database.save' => true]);
});

afterEach(function () {
    Schema::dropIfExists('products');
});

it('scans a url and returns the seo score', function () {
    $scan = SeoScanModel::create(['total_checks' => 1, 'started_at' => now()]);

    $seo = app(PageScanRunner::class)->scan($scan, 'https://example.com/');

    expect($seo)->toBeInstanceOf(SeoScore::class);
});

it('persists a seo score row for the scanned url', function () {
    $scan = SeoScanModel::create(['total_checks' => 1, 'started_at' => now()]);

    app(PageScanRunner::class)->scan($scan, 'https://example.com/');

    $row = DB::connection('testing')->table('seo_scores')->first();

    expect($row)->not->toBeNull()
        ->and($row->seo_scan_id)->toBe($scan->id)
        ->and($row->url)->toBe('https://example.com/')
        ->and($row->model_type)->toBeNull()
        ->and($row->model_id)->toBeNull();
});

it('persists the model reference when scanning a model instance', function () {
    $scan = SeoScanModel::create(['total_checks' => 1, 'started_at' => now()]);
    $product = Product::create(['url' => 'https://example.com/product/1']);

    app(PageScanRunner::class)->scan($scan, $product->url, $product);

    $row = DB::connection('testing')->table('seo_scores')->first();

    expect($row->model_type)->toBe($product->getMorphClass())
        ->and((int) $row->model_id)->toBe($product->id);
});

it('does not persist when database saving is disabled', function () {
    config(['seo.database.save' => false]);
    $scan = SeoScanModel::create(['total_checks' => 1, 'started_at' => now()]);

    app(PageScanRunner::class)->scan($scan, 'https://example.com/');

    expect(DB::connection('testing')->table('seo_scores')->count())->toBe(0);
});
