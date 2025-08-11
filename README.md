# Laravel SEO Scanner

[![Total Downloads](https://img.shields.io/packagist/dt/backstagephp/laravel-seo-scanner.svg?style=flat-square)](https://packagist.org/packages/backstagephp/laravel-seo-scanner)
[![Tests](https://github.com/backstagephp/laravel-seo-scanner/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/backstagephp/laravel-seo-scanner/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/backstagephp/laravel-seo-scanner/actions/workflows/phpstan.yml/badge.svg?branch=main)](https://github.com/backstagephp/laravel-seo-scanner/actions/workflows/phpstan.yml)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/backstagephp/laravel-seo-scanner)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/backstagephp/laravel-seo-scanner)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/backstagephp/laravel-seo-scanner.svg?style=flat-square)](https://packagist.org/packages/backstagephp/laravel-seo-scanner)

## Nice to meet you, we're [Backstage](https://backstagephp.com)

Hi! We are a web development agency from Nijmegen in the Netherlands and we use Laravel for everything: advanced websites with a lot of bells and whitles and large web applications.

## The Laravel tool to boost the SEO score of your web pages

![Screenshot 2023-01-05 at 15 02 31](https://user-images.githubusercontent.com/10845460/210797960-d65e260e-d543-4aec-aca8-1d9cca3aee96.png)

## Introduction

This package is your guidance to get a better SEO score on search engines. Laravel SEO Scanner scans your code and crawls the routes from your app. The package has 24 checks that will check on performance, configurations, use of meta tags and content quality.

Easily configure which routes to scan, exclude or include specific checks or even add your own checks! Completing checks will further improve the SEO score and thus increase the chance of ranking higher at the search engines.

-   [Minimum requirements](#minimum-requirements)
-   [Installation](#installation)
-   [Available checks](#available-checks)
    -   [Configuration](#configuration)
    -   [Content](#content)
    -   [Meta](#meta)
    -   [Performance](#performance)
-   [Usage](#usage)
    -   [Running the scanner in a local environment](#running-the-scanner-in-a-local-environment)
    -   [Scanning routes](#scanning-routes)
    -   [Scanning a single route](#scanning-a-single-route)
    -   [Scanning routes in an SPA application](#scanning-routes-in-an-spa-application)
    -   [Throttling](#throttling)
    -   [Scan model urls](#scan-model-urls)
    -   [Saving scans into the database](#saving-scans-into-the-database)
    -   [Listening to events](#listening-to-events)
    -   [Retrieving scans](#retrieving-scans)
    -   [Retrieving scores](#retrieving-scores)
    -   [Adding your own checks](#adding-your-own-checks)
-   [Testing](#testing)
-   [Changelog](#changelog)
-   [Contributing](#contributing)
-   [Security Vulnerabilities](#security-vulnerabilities)
-   [Credits](#credits)
-   [License](#license)

## Minimum requirements

-   PHP 8.1 or higher (8.1, 8.2, 8.3, 8.4)
-   Laravel 9.0 or higher (9.x, 10.x, 11.x, 12.x)

## Installation

You can install the package via composer:

```bash
composer require backstage/laravel-seo-scanner
```

If you want to scan pages that are rendered using Javascript, for example Vue or React, you need to install Puppeteer. You can install it using the following command:

> If you want to know how to scan Javascript rendered pages, check out [Scanning routes in an SPA application](#scanning-routes-in-an-spa-application). Want to know more about Puppeteer? Check out the [Puppeteer documentation](https://pptr.dev/).

```bash
npm install puppeteer
```

Run the install command to publish the config file and run the migrations:

```bash
php artisan seo:install
```

Or you can publish the config file and run the migrations manually:

```bash
php artisan vendor:publish --tag="seo-migrations"
php artisan migrate
```

```bas
php artisan vendor:publish --tag="seo-config"
```

Click here to see the [config file](https://github.com/backstagephp/laravel-seo-scanner/blob/too-long-sentences-check/config/seo.php).

## Available checks

These checks are available in the package. You can add or remove checks in the config file. These checks are based on SEO best practices and if all checks are green, your website will have a good SEO score. If you want to add more checks, you can create a pull request.

### Configuration

✅ The page does not have 'noindex' set. <br>
✅ The page does not have 'nofollow' set. <br>
✅ Robots.txt allows indexing. <br>

### Content

✅ The page has an H1 tag and if it is used only once per page. <br>
✅ All links redirect to an url using HTTPS. <br>
✅ Every image has an alt attribute. <br>
✅ The page contains no broken links. <br>
✅ The page contains no broken images. <br>
✅ Length of the content is at least 2100 characters. <br>
✅ No more than 20% of the content contains too long sentences (more than 20 words). <br>
✅ A minimum of 30% of the sentences contain a transition word or phrase. <br>

> Note: To change the locale of the transition words, you can publish the config file and change the locale in the config file. The default locale is `null` which uses the language of your `app` config. If set to `nl` or `en`, the transition words will be in Dutch or English. If you want to add more locales, you can create a pull request.

### Meta

✅ The page has a meta description. <br>
✅ The page title is not longer than 60 characters. <br>
✅ The page has an Open Graph image.<br>
✅ The lang attribute is set on the html tag.<br>
✅ The title contains one or more keywords.<br>
✅ One or more keywords are present in the first paragraph.<br>

### Performance

✅ Time To First Byte (TTFB) is below 600ms. <br>
✅ The page response returns a 200 status code. <br>
✅ HTML is not larger than 100 KB. <br>
✅ Images are not larger than 1 MB. <br>
✅ JavaScript files are not larger than 1 MB. <br>
✅ CSS files are not larger than 15 KB. <br>
✅ HTML is GZIP compressed. <br>

## Usage

### Running the scanner in a local environment

If you are using auto signed SSL certificates in your local development environment, you may want to disable the SSL certificate integrity check. You can do this by adding the following option to the `http.options` array in the config file:

```php
'http' => [
    'options' => [
        'verify' => false,
    ],
],
```

It's also possible to pass custom headers to the http client. For example, if you want to set a custom user agent, you can add the following option to the `http.headers` array in the config file:

```php
'http' => [
    'headers' => [
        'User-Agent' => 'My custom user agent',
    ],
],
```

### Scanning routes

By default, all `GET` routes will be checked for SEO. If you want to check the SEO score of a specific route, you can add the route name to the `routes` array in the config file. If you want to skip a route, you can add the route name to the `exclude_routes` array in the config file. If you don't want to check the SEO score of routes at all, you can set the `check_routes` option to `false` in the config file.

To check the SEO score of your routes, run the following command:

```bash
php artisan seo:scan
```

If you want to queue the scan and trigger it manually you can dispatch the 'Scan' job:

```php
use Backstage\LaravelSeo\Jobs\Scan;

Scan::dispatch();
```

### Scanning a single route

Want to get the score of a specific url? Run the following command:

```bash
php artisan seo:scan-url https://backstagephp.com
```

> Note: The command will only check the SEO score of the url and output the score in the CLI. It will not save the score to the database.

### Scanning routes in an SPA application

If you have an SPA application, you can enable javascript rendering. This will use a headless browser to render the content. To enable javascript rendering, set the `javascript` option to `true` in the config file. You can also enable javascript rendering for a single route by adding the `--javascript` option to the command:

```bash
php artisan seo:scan-url https://backstagephp.com --javascript
```

> Note: This command will use Puppeteer to render the page. Make sure that you have Puppeteer installed on your system. You can install Puppeteer by running the following command: `npm install puppeteer`. **At this moment it's only available when scanning single routes.**

### Throttling

If you want to throttle the requests, you can set the `throttle` option to `true` in the config file. You can also set the amount of requests per minute by setting the `requests_per_minute` option in the config file.

```php
'throttle' => [
    'enabled' => false,
    'requests_per_minute' => 10,
],
```

### Scan model urls

When you have an application where you have a lot of pages which are related to a model, you can save the SEO score to the model. This way you can check the SEO score of a specific page and show it in your application.

For example, you have a `BlogPost` model which has a page for each content item:

1. Add the model to the `models` array in the config file.
2. Implement the `SeoInterface` in your model.
3. Add the `HasSeoScore` trait to your model.

> Note: Please make sure that the model has a `url` attribute. This attribute will be used to check the SEO score of the model. Also check that the migrations are run. Otherwise the command will fail.

```php

use Backstage\Seo\Traits\HasSeoScore;
use Backstage\Seo\SeoInterface;

class BlogPost extends Model implements SeoInterface
{
    use HasFactory,
        HasSeoScore;

    protected $fillable = [
        'title',
        'description',
        'slug',
        // ...
    ];

    public function getUrlAttribute(): string
    {
        return 'https://backstagephp.com/' . $this->slug;
    }
}
```

You can get the SEO score of a model by calling the `seoScore()` or `seoScoreDetails()` methods on the model. These methods are defined in the `HasSeoScore` trait and can be overridden by adding the modified method in your model.

To fill the database with the scores of all models, run the following command:

```bash
php artisan seo:scan
```

To get the SEO score(s) of a model, you have the following options:

1. Get the SEO scores of a single model from the database:

```php
$scores = Model::withSeoScores()->get();
```

2. Run a SEO score check on a single model:

```php
$model = Model::first();

// Get just the score
$score = $model->getCurrentScore();

// Get the score including the details
$scoreDetails = $model->getCurrentScoreDetails();
```

### Saving scans into the database

When you want to save the SEO score to the database, you need to set the `save` option to `true` in the config file.

```php
'database' => [
    'connection' => 'mysql',
    'save' => true,
    'prune' => [
        'older_than_days' => 30,
    ],
],
```

Optionally you can specify the database connection in the config file. If you want to save the SEO score to a model, you need to add the model to the `models` array in the config file. More information about this can be found in the [Check the SEO score of a model](#check-the-seo-score-of-a-model) section.

#### Pruning the database

Per default the package will prune the database from old scans. You can specify the number of days you want to keep the scans in the database. The default is 30 days.

If you want to prune the database, you need to add the prune command to your `App\Console\Kernel`:

```php
protected function schedule(Schedule $schedule)
{
    // ...
    $schedule->command('model:prune')->daily();
}
```

Please refer to the [Laravel documentation](https://laravel.com/docs/10.x/eloquent#pruning-models) for more information about pruning the database.

### Listening to events

When you run the `seo:scan` command, the package will fire an event to let you know it's finished. You can listen to this events and do something with the data. For example, you can send an email to the administrator when the SEO score of a page is below a certain threshold. Add the following code to your `EventServiceProvider`:

```php

protected $listen = [
    // ...
    ScanCompleted::class => [
        // Add your listener here
    ],
];
```

### Retrieving scans

You can retrieve the scans from the database by using the `SeoScan` model. This model is used to save the scans to the database. You can use the `SeoScan` model to retrieve the scans from the database. For example:

```php
use Backstage\Seo\Models\SeoScan;

// Get the latest scan
$scan = SeoScan::latest()->first();

// Get the failed checks
$failedChecks = $scan->failedChecks;

// Get the total amount of pages scanned
$totalPages = $scan->pages;
```

### Retrieving scores

You can retrieve the scores from the database by using the `SeoScore` model. This model is used to save the scores to the database. You can use the `SeoScore` model to retrieve the scores from the database. For example:

```php
use Backstage\Seo\Models\SeoScore;

// Get the latest score
$score = SeoScore::latest()->first();

// Or get all scores for a specific scan
$scan = SeoScan::latest()->with('scores')->first();
```

### Adding your own checks

You can add your own checks to the package. To do this, you need to create a `check` class in your application.

1. Create a new class in your application which implements the `Backstage\Seo\Interfaces\Check` interface.
2. Add the `Backstage\Seo\Traits\PerformCheck` trait to your class.
3. Add the base path of your check classes to the `check_paths` array in the config file.

#### Example

In this example I make use of the `symfony/dom-crawler` package to crawl the HTML of a page as this is far more reliable than using `preg_match` for example. Feel free to use anything you want. The crawler is always passed to the `check` method, so you still need to define the `$crawler` parameter in your `check` method.

```php
<?php

namespace App\Support\Seo\Checks;

use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;
use Backstage\Seo\Interfaces\Check;
use Backstage\Seo\Traits\PerformCheck;

class CanonicalCheck implements Check
{
    use PerformCheck;

    /**
     * The name of the check.
     */
    public string $title = "The page has a canonical meta tag";

    /**
     * The priority of the check (in terms of SEO).
     */
    public string $priority = 'low';

    /**
     * The time it takes to fix the issue.
     */
    public int $timeToFix = 1;

    /**
     * The weight of the check. This will be used to calculate the score.
     */
    public int $scoreWeight = 2;

    /**
     * If this check should continue after a failure. You don't
     * want to continue after a failure if the page is not
     * accessible, for example.
     */
    public bool $continueAfterFailure = true;

    public string|null $failureReason;

    /* If you want to check the actual value later on make sure
     * to set the actualValue property. This will be used
     * when saving the results.
     */
    public mixed $actualValue = null;

    /* If you want to check the expected value later on make sure
     * to set the expectedValue property. This will be used
     * when saving the results.
     */
    public mixed $expectedValue = null;

    public function check(Response $response, Crawler $crawler): bool
    {
        // Feel free to use any validation you want here.
        if (! $this->validateContent($crawler)) {
            return false;
        }

        return true;
    }

    public function validateContent(Crawler $crawler): bool
    {
        // Get the canonical meta tag
        $node = $crawler->filterXPath('//link[@rel="canonical"]')->getNode(0);

        if (! $node) {
            // We set the failure reason here so this will be showed in the CLI and saved in the database.
            $this->failureReason = 'The canonical meta tag does not exist';
            return false;
        }

        // Get the href attribute
        $this->actualValue = $node->getAttribute('href');

        if (! $this->actualValue) {
            // The failure reason is different here because the canonical tag exists, but it does not have a href attribute.
            $this->failureReason = 'The canonical meta tag does not have a href attribute';

            return false;
        }

        // The canonical meta tag exists and has a href attribute, so the check is successful.
        return true;
    }
}
```

The config file:

```php
return [
    // ...
    'check_paths' => [
        'Backstage\\Seo\\Checks' => base_path('vendor/backstagephp/laravel-seo-scanner/src/Checks'),
        'App\\Support\\Seo\\Checks' => base_path('app/Support/Seo/Checks'),
    ],
];
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Bas van Dinther](https://github.com/Baspa)
-   [Mark van Eijk](https://github.com/markvaneijk)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
