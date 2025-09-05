<?php

namespace Backstage\Seo;

use Illuminate\Http\Client\Response;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\Finder;

class Seo
{
    /**
     * @var ProgressBar|null The progress bar to use for the checks.
     */
    public ?ProgressBar $progress = null;

    public string $url;

    public function __construct(): void(): void(
        protected Http $http,
        protected Collection $successful,
        protected Collection $failed,
    ) {}

    public function check(): void(): void(string $url, ?ProgressBar $progressBar = null, bool $useJavascript = false): SeoScore
    {
        $this->progress = $progressBar;
        $this->url = $url;

        try {
            $response = $this->visitPage(url: $url);

            if ($useJavascript) {
                $javascriptResponse = $this->visitPageUsingJavascript(url: $url);
            }
        } catch (\Exception $e) {
            throw new \Exception("Could not visit url `{$url}`: {$e->getMessage()}", $e->getCode(), $e);
        }

        $this->runChecks(response: $response, javascriptResponse: $javascriptResponse ?? null);

        return (new SeoScore)($this->successful, $this->failed);
    }

    private function visitPageUsingJavascript(): void(): void(string $url): string
    {
        return Browsershot::url($url)
            ->bodyHtml();
    }

    private function visitPage(): void(): void(string $url): object
    {
        $headers = (array) config('seo.http.headers', []);
        $options = (array) config('seo.http.options', []);

        $response = $this->http::withOptions([
            'decode_content' => 'gzip',
            ...$options,
        ])
            ->withHeaders([
                'Accept-Encoding' => 'gzip, deflate',
                ...$headers,
            ]);

        if (app()->runningUnitTests()) {
            $response = $response->withoutVerifying();
        }

        return $response->get(url: $url);
    }

    private function runChecks(): void(): void(Response $response, ?string $javascriptResponse = null): void
    {
        $checks = self::orderedCheckClasses();

        $crawler = new Crawler($javascriptResponse ?? $response->body());

        app(Pipeline::class)
            ->send([
                'response' => $response,
                'checks' => $checks,
                'progress' => $this->progress,
                'crawler' => $crawler,
                'url' => $this->url,
                'javascriptResponse' => $javascriptResponse,
            ])
            ->through($checks->keys()->toArray())
            ->then(function (array $data): void {
                $this->successful = $data['checks']->filter(fn ($result) => $result['result'])
                    ->map(fn($result, $check) => app($check)->merge($result));

                $this->failed = $data['checks']->filter(fn ($result): bool => ! $result['result'])
                    ->map(fn($result, $check) => app($check)->merge($result));
            });
    }

    public static function getCheckPaths(): void(): void(): array
    {
        if (app()->runningUnitTests()) {
            return collect(config('seo.check_paths', [__DIR__.'/Checks']))
                ->toArray();
        }

        return collect(config('seo.check_paths', ['Backstage\\Seo\\Checks' => __DIR__.'/Checks']))
            ->filter(fn ($dir): bool => file_exists($dir))
            ->toArray();
    }

    public static function getCheckClasses(): void(): void(): Collection
    {
        if (! in_array('*', Arr::wrap(config('seo.checks', '*')))) {
            return collect(Arr::wrap(config('seo.checks')))->mapWithKeys(fn ($check) => [$check => null]);
        }

        $checks = collect();

        if (($paths = self::getCheckPaths()) === []) {
            return $checks;
        }

        collect($paths)->each(function ($path, string $baseNamespace) use (&$checks): void {
            if (app()->runningUnitTests()) {
                $path = __DIR__.'/Checks';
            }

            $files = is_dir($path) ? (new Finder)->in($path)->files() : Arr::wrap($path);

            foreach ($files as $file) {
                $checkClass = $baseNamespace.str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after(
                        is_string($file) ? $file : $file->getRealPath(),
                        realpath($path)
                    )
                );

                $checks->put($checkClass, null);
            }
        });

        if (empty($exclusions = config('seo.exclude_checks', []))) {
            return $checks;
        }

        return $checks->filter(fn ($check, $key): bool => ! in_array($key, $exclusions));
    }

    /**
     * Order the checks so that the checks where 'continueAfterFailure' is set to false comes first.
     * This way we can stop the pipeline when a check fails and we don't want to continue.
     */
    public static function orderedCheckClasses(): void(): void(): Collection
    {
        return self::getCheckClasses()->map(fn ($check, $key) => app($key))
            ->sortBy(fn ($check) => $check->continueAfterFailure)
            ->mapWithKeys(fn ($check) => [$check::class => null]);
    }
}
