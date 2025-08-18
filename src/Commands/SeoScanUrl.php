<?php

namespace Backstage\Seo\Commands;

use Backstage\Seo\Facades\Seo;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\text;

class SeoScanUrl extends Command
{
    public $signature = 'seo:scan-url {url?} {--javascript}';

    public $description = 'Scan the SEO score of a url';

    public function handle(): int
    {
        $url = $this->argument('url') ?? text(
            label: 'Pleaes enter the url',
            validate: function (string $value) {
                try {
                    if (! \Illuminate\Support\Facades\Http::get($value)->successful()) {
                        return 'Please enter a valid url.';
                    }

                    return null;
                } catch (\Exception $e) {
                    return 'Please enter a valid url.';
                }
            }
        );

        $useJavascript = $this->option('javascript') ?
            $this->option('javascript') :
            confirm(
                label: 'Do you want to use JavaScript?',
                default: true,
                yes: 'I do',
                no: 'I dont'
            );

        $progress = progress(label: 'Please wait while we scan your web page...', steps: getCheckCount(), hint: $url);
        $progress->start();

        $score = Seo::check($url, $progress, $useJavascript);

        $progress->finish();

        $this->line('');
        $this->line('');
        $this->line('-----------------------------------------------------------------------------------------------------------------------------------');
        $this->line('> '.$url.' | <fg=green>'.$score->getSuccessfulChecks()->count().' passed</> <fg=red>'.($score->getFailedChecks()->count().' failed</>'));
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
