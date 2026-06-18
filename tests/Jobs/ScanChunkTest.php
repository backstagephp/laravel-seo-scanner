<?php

use Backstage\Seo\Checks\Content\MultipleHeadingCheck;
use Backstage\Seo\Jobs\ScanChunk;
use Backstage\Seo\Models\SeoScan as SeoScanModel;
use Backstage\Seo\SeoScore;
use Backstage\Seo\Services\PageScanRunner;
use Backstage\Seo\Tests\Support\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Helper\ProgressBar;

beforeEach(function () {
    config(['seo.database.connection' => 'testing']);
    config(['seo.database.save' => true]);
    config(['seo.checks' => [MultipleHeadingCheck::class]]);
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

    Schema::create('products', function ($table) {
        $table->bigIncrements('id');
        $table->string('url')->nullable();
    });

    Http::fake([
        '*' => Http::response('<html><head><title>Test</title></head><body><h1>Test</h1></body></html>'),
    ]);
});

afterEach(function () {
    Schema::dropIfExists('products');
});

it('scans a chunk of urls and persists a score per url', function () {
    $scan = SeoScanModel::create(['total_checks' => 1, 'started_at' => now()]);

    (new ScanChunk(scanId: $scan->id, urls: [
        'https://example.com/a',
        'https://example.com/b',
    ]))->handle(app(PageScanRunner::class));

    expect(DB::connection('testing')->table('seo_scores')->count())->toBe(2);
});

it('continues scanning the chunk when one page throws', function () {
    $scan = SeoScanModel::create(['total_checks' => 1, 'started_at' => now()]);

    $runner = new class extends PageScanRunner
    {
        public array $scanned = [];

        public function scan(?SeoScanModel $scan, string $url, ?Model $model = null, bool $useJavascript = false, ?ProgressBar $progress = null): SeoScore
        {
            if (str_contains($url, 'boom')) {
                throw new RuntimeException('kaboom');
            }

            $this->scanned[] = $url;

            return new SeoScore;
        }
    };

    (new ScanChunk(scanId: $scan->id, urls: [
        'https://example.com/a',
        'https://example.com/boom',
        'https://example.com/c',
    ]))->handle($runner);

    expect($runner->scanned)->toBe([
        'https://example.com/a',
        'https://example.com/c',
    ]);
});

it('scans a chunk of model records by id and persists the model reference', function () {
    $scan = SeoScanModel::create(['total_checks' => 1, 'started_at' => now()]);
    $a = Product::create(['url' => 'https://example.com/1']);
    $b = Product::create(['url' => 'https://example.com/2']);

    (new ScanChunk(scanId: $scan->id, model: Product::class, ids: [$a->id, $b->id]))
        ->handle(app(PageScanRunner::class));

    $rows = DB::connection('testing')->table('seo_scores')->get();

    expect($rows)->toHaveCount(2)
        ->and($rows->pluck('model_type')->unique()->all())->toBe([$a->getMorphClass()]);
});
