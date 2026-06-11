<?php

namespace Backstage\Seo\Commands;

use Backstage\Seo\Facades\Seo;
use Backstage\Seo\SeoScore;
use Illuminate\Console\Command;

class SeoScanUrl extends Command
{
    public $signature = 'seo:scan-url {url} {--javascript} {--format=console : The output format (console or json)}';

    public $description = 'Scan the SEO score of a url';

    public function handle(): int
    {
        $json = $this->option('format') === 'json';

        if (! $json) {
            $this->info('Please wait while we scan your web page...');
            $this->line('');
        }

        $progress = $json ? null : $this->output->createProgressBar(getCheckCount());
        $progress?->start();

        $score = Seo::check($this->argument('url'), $progress, $this->option('javascript'));

        $progress?->finish();

        if ($json) {
            return $this->outputJson($score);
        }

        return $this->outputConsole($score);
    }

    private function outputJson(SeoScore $score): int
    {
        $hasChecks = $score->getSuccessfulChecks()->isNotEmpty() || $score->getFailedChecks()->isNotEmpty();

        $payload = array_merge(['url' => $this->argument('url')], $score->toArray());

        $this->output->writeln(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        cache()->driver(config('seo.cache.driver'))->tags('seo')->flush();

        return $hasChecks ? self::SUCCESS : self::FAILURE;
    }

    private function outputConsole(SeoScore $score): int
    {
        $this->line('');
        $this->line('');
        $this->line('-----------------------------------------------------------------------------------------------------------------------------------');
        $this->line('> '.$this->argument('url').' | <fg=green>'.$score->getSuccessfulChecks()->count().' passed</> <fg=red>'.($score->getFailedChecks()->count().' failed</>'));
        $this->line('-----------------------------------------------------------------------------------------------------------------------------------');
        $this->line('');

        // If successful and failed checks are empty, we can assume that the
        // visit page threw an exception. In that case, we don't want to
        // show the checks. But show the exception message instead.
        if ($score->getSuccessfulChecks()->isEmpty() && $score->getFailedChecks()->isEmpty()) {
            $this->line('<fg=red>✘ Unfortunately, the url you entered is not correct. Please try again with a different url.</>');

            return self::FAILURE;
        }

        $score->getAllChecks()->each(function ($checks, $type) {
            $checks->each(function ($check) use ($type) {
                if ($type == 'failed') {
                    $this->line('<fg=red>✘ '.$check->title.' failed.</>');

                    if (property_exists($check, 'failureReason')) {
                        $this->line($check->failureReason.' Estimated time to fix: '.$check->timeToFix.' minute(s).');

                        $this->line('');
                    }
                } else {
                    $this->line('<fg=green>✔ '.$check->title.'</>');
                }
            });

            $this->line('');
        });

        $totalChecks = $score->getFailedChecks()->count() + $score->getSuccessfulChecks()->count();

        $this->info('Completed '.$totalChecks.' out of '.getCheckCount().' checks.');
        $this->line('');

        cache()->driver(config('seo.cache.driver'))->tags('seo')->flush();

        return self::SUCCESS;
    }
}
