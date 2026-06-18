<?php

use Backstage\Seo\Checks\Content\MultipleHeadingCheck;
use Backstage\Seo\Facades\Seo;
use Backstage\Seo\Tests\Support\ThrowingCheck;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['seo.database.save' => false]);
    config(['seo.checks' => [
        ThrowingCheck::class,
        MultipleHeadingCheck::class,
    ]]);

    Http::fake([
        '*' => Http::response('<html><head><title>Test</title></head><body><h1>Test</h1></body></html>'),
    ]);
});

it('records a throwing check as failed without aborting the rest of the scan', function () {
    $score = Seo::check('https://example.com');

    expect($score->getFailedChecks()->keys())->toContain(ThrowingCheck::class)
        ->and($score->getFailedChecks()->get(ThrowingCheck::class)->failureReason)
        ->toBe(__('failed.check.error'));
});

it('still runs the remaining checks after one check throws', function () {
    $score = Seo::check('https://example.com');

    expect($score->getSuccessfulChecks()->keys())->toContain(MultipleHeadingCheck::class);
});
