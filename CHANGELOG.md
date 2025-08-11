# Changelog

All notable changes to `laravel-seo` will be documented in this file.

## v3.2.0 - 2024-12-19

### What's Changed

-   Add support for PHP 8.3 and 8.4
-   Ensure full Laravel 12.x compatibility
-   Update GitHub Actions workflows to test against new PHP versions

**Full Changelog**: https://github.com/backstagephp/laravel-seo-scanner/compare/v3.1.0...v3.2.0

## v3.1.0 - 2023-08-17

**Full Changelog**: https://github.com/backstagephp/laravel-seo-scanner/compare/v3.0.0...v3.1.0

## v3.0.0 - 2023-08-17

### What's Changed

-   Crawl Javascript pages (SPA) by @Baspa in https://github.com/backstagephp/laravel-seo-scanner/pull/39

**Full Changelog**: https://github.com/backstagephp/laravel-seo-scanner/compare/v2.6.1...v3.0.0

## v2.6.1 - 2023-08-04

### What's Changed

-   Keyword checks by @Baspa in https://github.com/backstagephp/laravel-seo-scanner/pull/36

**Full Changelog**: https://github.com/backstagephp/laravel-seo-scanner/compare/v2.5.0...v2.6.1

## v2.5.0 - 2023-08-03

### What's Changed

-   Remove title check by @Baspa in https://github.com/backstagephp/laravel-seo-scanner/pull/35

**Full Changelog**: https://github.com/backstagephp/laravel-seo-scanner/compare/v2.4.0...v2.5.0

## v2.4.0 - 2023-08-03

### What's Changed

-   Bump dependabot/fetch-metadata from 1.5.1 to 1.6.0 by @dependabot in https://github.com/backstagephp/laravel-seo-scanner/pull/34
-   Replace cURL with HTTP by @Baspa in https://github.com/backstagephp/laravel-seo-scanner/pull/33

**Full Changelog**: https://github.com/backstagephp/laravel-seo-scanner/compare/v2.3.0...v2.4.0

## v2.3.0 - 2023-07-03

Broken image check now also saves the status in the database.

**Full Changelog**: https://github.com/backstagephp/laravel-seo-scanner/compare/v2.2.3...v2.3.0

## v2.2.0 - 2023-05-29

### What's Changed

-   Bump dependabot/fetch-metadata from 1.4.0 to 1.5.1 by @dependabot in https://github.com/backstagephp/laravel-seo-scanner/pull/31
-   Bump aglipanci/laravel-pint-action from 2.2.0 to 2.3.0 by @dependabot in https://github.com/backstagephp/laravel-seo-scanner/pull/32
-   [Feature] Domain custom resolve by @markvaneijk in https://github.com/backstagephp/laravel-seo-scanner/pull/22

### New Contributors

-   @markvaneijk made their first contribution in https://github.com/backstagephp/laravel-seo-scanner/pull/22

**Full Changelog**: https://github.com/backstagephp/laravel-seo-scanner/compare/v2.1.0...v2.2.0

## v2.1.0 - 2023-05-29

### What's Changed

-   Prunable scans by @Baspa in https://github.com/backstagephp/laravel-seo-scanner/pull/30

**Full Changelog**: https://github.com/backstagephp/laravel-seo-scanner/compare/v2.0.0...v2.1.0

## v2.0.0 - 2023-05-26

### Breaking changes

The following changes have been made to classnames with abbreviations in the checks:

-   `CCSSizeCheck` has been changed to `CssSizeCheck`
-   `TTFBSizeCheck` has been changed to `TtfbSizeCheck`
-   `HTMLSizeCheck` has been changed to `HtmlSizeCheck`

If you have scans saved in the database, please ensure you update the failed_checks column in the seo_scans table. The previous namespaces used in this column are no longer compatible.

### What's Changed

-   Bump dependabot/fetch-metadata from 1.3.6 to 1.4.0 by @dependabot in https://github.com/backstagephp/laravel-seo-scanner/pull/27
-   Save broken link status code by @Baspa in https://github.com/backstagephp/laravel-seo-scanner/pull/28
-   Reformat to abbrevation to lowercase by @Baspa in https://github.com/backstagephp/laravel-seo-scanner/pull/29

**Full Changelog**: https://github.com/backstagephp/laravel-seo-scanner/compare/v1.3.0...v2.0.0

## v1.3.0 - 2023-05-05

### What's Changed

-   Bump aglipanci/laravel-pint-action from 2.1.0 to 2.2.0 by @dependabot in https://github.com/backstagephp/laravel-seo-scanner/pull/25
-   Run scan with queued job by @Baspa in https://github.com/backstagephp/laravel-seo-scanner/pull/26

**Full Changelog**: https://github.com/backstagephp/laravel-seo-scanner/compare/v1.2.0...v1.3.0

## v1.2.0 - 2023-03-09

### What's Changed

-   Adding a new config to disable SSL certificate integrity check by @MuriloChianfa in https://github.com/backstagephp/laravel-seo-scanner/pull/18
-   Bump dependabot/fetch-metadata from 1.3.5 to 1.3.6 by @dependabot in https://github.com/backstagephp/laravel-seo-scanner/pull/20
-   [Feature] Laravel 10 compatibility by @Baspa in https://github.com/backstagephp/laravel-seo-scanner/pull/24

### New Contributors

-   @MuriloChianfa made their first contribution in https://github.com/backstagephp/laravel-seo-scanner/pull/18

**Full Changelog**: https://github.com/backstagephp/laravel-seo-scanner/compare/v1.1.0...v1.2.0

## v1.1.0 - 2023-01-13

### What's Changed

-   Fix Windows tests by removing SSL validation when using `curl()` by @Baspa in https://github.com/backstagephp/laravel-seo-scanner/pull/16
-   Check if a relative url is used when performing broken link checks by @Baspa in https://github.com/backstagephp/laravel-seo-scanner/pull/17

**Full Changelog**: https://github.com/backstagephp/laravel-seo-scanner/compare/v1.0.1...v1.1.0

## v1.0.0 - 2023-01-06

### Laravel SEO Scanner v1.0.0 released

🚀 Vormkracht10 proudly presents the first release of the Laravel SEO Scanner. This release includes basic features to get started scanning your Laravel application routes for SEO improvements. With this release we have a solid basement to build upon further.

#### Features

-   Total of 21 checks
-   Extensive config file where you can configure how the package behaves.
-   Command to check all configured routes
-   Command to check a single url
-   Possibility to save results into the database
-   Corresponding models to easily retrieve the data

**Full Changelog**: https://github.com/backstagephp/laravel-seo-scanner/commits/v1.0.0
