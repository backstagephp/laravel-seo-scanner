<?php

namespace Backstage\Seo;

use Backstage\Seo\Commands\SeoScan;
use Backstage\Seo\Commands\SeoScanUrl;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SeoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(): void(Package $package): void
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
            ->hasInstallCommand(function (InstallCommand $installCommand): void {
                $installCommand
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('backstage/laravel-seo');
            });
    }
}
