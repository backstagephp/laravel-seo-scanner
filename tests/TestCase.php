<?php

namespace Backstage\Seo\Tests;

use Backstage\Seo\SeoServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public static $latestResponse;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Backstage\\Seo\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        // Fail fast and loud if a test makes an HTTP request it didn't fake,
        // instead of silently hitting the live network and timing out
        // intermittently in CI. Tests that need HTTP must Http::fake() it.
        Http::preventStrayRequests();
    }

    protected function getPackageProviders($app)
    {
        return [
            SeoServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-seo_table.php.stub';
        $migration->up();
        */
    }
}
