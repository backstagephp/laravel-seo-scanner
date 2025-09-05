<?php

namespace Backstage\Seo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class Scan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $timeout = 60 * 60 * 3;

    public function handle(): void(?string $url = null): void
    {
        if (! $url) {
            Artisan::call('seo:scan');

            return;
        }

        Artisan::call('seo:scan-url', [
            'url' => $url,
        ]);
    }
}
