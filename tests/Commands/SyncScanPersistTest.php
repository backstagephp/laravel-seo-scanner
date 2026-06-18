<?php

use Backstage\Seo\Checks\Content\MultipleHeadingCheck;
use Backstage\Seo\Models\SeoScan as SeoScanModel;
use Backstage\Seo\Tests\Support\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['seo.database.connection' => 'testing']);
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

    Schema::create('products', function ($table) {
        $table->bigIncrements('id');
        $table->string('url')->nullable();
    });

    Http::fake([
        '*' => Http::response('<html><head><title>Test</title></head><body><h1>Test</h1></body></html>'),
    ]);

    config(['seo.checks' => [MultipleHeadingCheck::class]]);
    config(['seo.check_routes' => false]);
    config(['seo.models' => [Product::class]]);
    config(['seo.database.save' => true]);
});

afterEach(function () {
    Schema::dropIfExists('products');
});

it('persists a seo score row per model record and finalizes the scan record', function () {
    foreach (range(1, 3) as $i) {
        Product::create(['url' => "https://example.com/product/{$i}"]);
    }

    $this->artisan('seo:scan')->assertExitCode(0);

    expect(DB::connection('testing')->table('seo_scores')->count())->toBe(3);

    $scan = SeoScanModel::on('testing')->latest('id')->first();

    expect($scan->pages)->toBe(3)
        ->and($scan->finished_at)->not->toBeNull()
        ->and($scan->failed_checks)->toBeArray();
});
