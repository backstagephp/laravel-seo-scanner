<?php

use Backstage\Seo\Http;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

if (! function_exists('isBrokenLink')) {
    function isBrokenLink(string $url): bool
    {
        $statusCode = (string) getRemoteStatus($url);

        if (! empty(config('seo.broken_link_check.status_codes'))) {
            return in_array($statusCode, config('seo.broken_link_check.status_codes'));
        }

        if (str_starts_with($statusCode, '4') || str_starts_with($statusCode, '5') || $statusCode == '0') {
            return true;
        }

        return false;
    }
}

if (! function_exists('getRemoteStatus')) {
    function getRemoteStatus(string $url): int
    {
        return cache()->driver(config('seo.cache.driver'))->tags('seo')->rememberForever($url, function () use ($url) {
            try {
                $response = Http::make($url)->getRemoteResponse();
            } catch (\Exception $e) {
                return 0;
            }

            $statusCode = $response->status();

            return $statusCode;
        });
    }
}

if (! function_exists('http_build_headers')) {
    function http_build_headers(array $headers): array
    {
        return array_map(fn ($value, $header): string => $header.': '.$value, array_values($headers), array_keys($headers));
    }
}

if (! function_exists('getRemoteFileSize')) {
    function getRemoteFileSize(string $url): int
    {
        return cache()->driver(config('seo.cache.driver'))->tags('seo')->rememberForever($url.'.size', function () use ($url) {

            try {
                $response = Http::make($url)->getRemoteResponse();
            } catch (\Exception $e) {
                return 0;
            }

            $response = $response->body();

            if (empty($response)) {
                return 0;
            }

            if (
                preg_match('/Content-Length: (\d+)/', $response, $matches) ||
                preg_match('/content-length: (\d+)/', $response, $matches)
            ) {
                $contentLength = (int) $matches[1];
            }

            if (! isset($contentLength)) {
                $options = (array) config('seo.http.options', []);
                $context = isset($options['verify']) && ! $options['verify']
                    ? $context = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]
                    : [];

                $contentLength = strlen(file_get_contents($url, false, stream_context_create($context)));
            }

            return $contentLength;
        });
    }
}

if (! function_exists('getCheckCount')) {
    function getCheckCount(): int
    {
        $checks = collect();

        collect(config('seo.check_paths', ['Backstage\\Seo\\Checks' => __DIR__.'/Checks']))
            ->each(function ($path, $baseNamespace) use (&$checks) {
                if (app()->runningUnitTests()) {
                    $path = __DIR__.'/Checks';
                }

                $files = is_dir($path) ? (new Finder)->in($path)->files() : Arr::wrap($path);

                foreach ($files as $fileInfo) {
                    $checkClass = $baseNamespace.str_replace(
                        ['/', '.php'],
                        ['\\', ''],
                        Str::after(
                            is_string($fileInfo) ? $fileInfo : $fileInfo->getRealPath(),
                            realpath($path)
                        )
                    );

                    $checks->put($checkClass, null);
                }
            });

        $checks = $checks->except(config('seo.exclude_checks', []));

        if (empty(config('seo.checks')) || ! in_array('*', config('seo.checks'))) {
            $checks = $checks->only(config('seo.checks'));
        }

        return $checks->count();
    }
}

if (! function_exists('bytesToHumanReadable')) {
    function bytesToHumanReadable(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        /**
         * According to the International System of Units (SI), kilo prefix is specified as 1000 (103).
         * Based on this, 1 kilobyte is equal to 1000 bytes. So we use 1000 instead of 1024.
         */
        $i = (int) floor(log($bytes, 1000));

        return round($bytes / (1000 ** $i), 2).' '.$units[$i];
    }
}

if (! function_exists('addBaseIfRelativeUrl')) {
    function addBaseIfRelativeUrl(string $url, ?string $checkedUrl = null): string
    {
        if (! Str::startsWith($url, '/')) {
            return $url;
        }

        if ($checkedUrl) {
            $parsedUrl = parse_url($checkedUrl);

            if ($parsedUrl) {
                return $parsedUrl['scheme'].'://'.$parsedUrl['host'].'/'.ltrim($url, '/');
            }
        }

        if (config('app.url')) {
            return config('app.url').$url;
        }

        return $url;
    }
}
