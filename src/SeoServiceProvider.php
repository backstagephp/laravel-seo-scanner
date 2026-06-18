<?php

namespace Backstage\Seo;

use Backstage\Seo\Commands\SeoScan;
use Backstage\Seo\Commands\SeoScanUrl;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SeoServiceProvider extends PackageServiceProvider
{
    public function packageBooted(): void
    {
        RateLimiter::for('seo-scan', function () {
            if (! config('seo.throttle.enabled') || ! config('seo.throttle.requests_per_minute')) {
                return Limit::none();
            }

            $chunkSize = max(1, (int) config('seo.chunk_size', 100));
            $jobsPerMinute = max(1, intdiv((int) config('seo.throttle.requests_per_minute'), $chunkSize));

            return Limit::perMinute($jobsPerMinute);
        });
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-seo')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations(['create_seo_scans_table', 'create_seo_score_table'])
            ->hasCommands([
                SeoScan::class,
                SeoScanUrl::class,
            ]);

        // When testing, we can ignore this code
        if (app()->runningUnitTests()) {
            return;
        }

        $package
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('backstage/laravel-seo');
            });
    }
}
