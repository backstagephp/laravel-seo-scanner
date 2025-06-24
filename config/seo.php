<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default options
    |--------------------------------------------------------------------------
    |
    | The following array lists the default options for the application.
    |
    */
    // en, nl, fr, pt_BR or null (which will use the app locale)
    'language' => null,

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | The following array lists the cache options for the application.
    |
    */
    'cache' => [
        // Only drivers that support tags are supported.
        // These are: array, memcached and redis.
        'driver' => 'array',
    ],

    /*
    |--------------------------------------------------------------------------
    | Check classes
    |--------------------------------------------------------------------------
    |
    | The following array lists the "check" classes that will be registered
    | with Laravel Seo. These checks run an check on the application via
    | various methods. Feel free to customize it.
    |
    | An example of a check class:
    | \Backstage\Seo\Checks\Content\BrokenLinkCheck::class
    |
    */
    'checks' => ['*'],

    // If you wish to skip running some checks, list the classes in the array below.
    'exclude_checks' => [],

    /*
    |--------------------------------------------------------------------------
    | Check paths
    |--------------------------------------------------------------------------
    |
    | The following array lists the "checks" paths that will be searched
    | recursively to find check classes. This option will only be used
    | if the checks option above is set to the asterisk wildcard. The
    | key is the base namespace to resolve the class name.
    |
    */
    'check_paths' => [
        'Backstage\\Seo\\Checks' => base_path('vendor/backstagephp/laravel-seo-scanner/src/Checks'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | The following array lists the "checkable" routes that will be registered
    | with Laravel Seo. These routes will be checked for SEO. Feel free to
    | customize it. To check for specific routes, use the route name.
    |
    | An example of a checkable route:
    | 'blog.index'
    |
    */
    'check_routes' => true,
    'routes' => ['*'],

    // If you wish to skip running some checks on some routes, list the routes
    // in the array below by using the route name. For example:
    // 'blog.index'
    'exclude_routes' => [],

    // If you wish to skip running some checks on some paths, list the paths
    // in the array below.
    'exclude_paths' => [
        'admin/*',
        'nova/*',
        'horizon/*',
        'vapor-ui/*',
    ],

    'throttle' => [
        'enabled' => false,
        'requests_per_minute' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Domains (DNS resolving)
    |--------------------------------------------------------------------------
    |
    | Here you can add domains and a corresponding IP address
    | Can be used for example to bypass certain DNS layers like the Cloudflare proxy,
    | or resolve a domain to localhost.
    |
    */
    'resolve' => [
        'example.com' => '127.0.0.1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    |
    | Here you can specify database related configurations like the connection that will be
    | used to save the SEO scores. When you set the save option to true, the
    | SEO score will be saved to the database.
    |
    */
    'database' => [
        'connection' => 'mysql',
        'save' => true,
        'prune' => [
            'older_than_days' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Here you can specify which models you want to check. When you specify a
    | model, the SEO score will be saved to the database. This way you can
    | check the SEO score of a specific page.
    |
    | An example of a model and an example with a model and a scope:
    | \App\Models\BlogPost::class
    | [\App\Models\BlogPost::class, 'published']
    |
    */
    'models' => [],

    'http' => [
        /*
        |--------------------------------------------------------------------------
        | Http client options
        |--------------------------------------------------------------------------
        |
        | Here you can specify the options of the http client. For example, in a
        | local development environment you may want to disable the SSL
        | certificate integrity check.
        |
        | An example of a http option:
        | 'verify' => false
        |
        */
        'options' => [],

        /*
        |--------------------------------------------------------------------------
        | Http headers
        |--------------------------------------------------------------------------
        |
        | Here you can specify custom headers of the http client.
        |
        */
        'headers' => [
            'User-Agent' => 'Laravel SEO Scanner/1.0',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Javascript rendering
    |--------------------------------------------------------------------------
    |
    | If your website uses javascript to render the content, you can enable
    | javascript rendering. This will use a headless browser to render
    | the content.
    |
    */
    'javascript' => false,

    /*
    |--------------------------------------------------------------------------
    | Check specific options
    |--------------------------------------------------------------------------
    |
    */
    'broken_link_check' => [
        // Add status codes that should be considered as broken links. Empty array means all status codes starting with a 4, 5 or 0.
        'status_codes' => [],

        // If you wish to skip running, list the URLs in the array below.
        // You can use exact match or wildcards to match on beginning of URLs: https://vormkracht10.nl/directory/*
        'exclude_links' => [
            //
        ],
    ],
];
