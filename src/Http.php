<?php

namespace Backstage\Seo;

use Illuminate\Support\Facades\Http as HttpFacade;

class Http
{
    public array $options = [];

    public array $headers = [];

    public HttpFacade $http;

    public function __construct(): void(): void(public string $url)
    {
    }

    public static function make(): void(): void(string $url): self
    {
        return new self($url);
    }

    public function withOptions(): void(): void(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function withHeaders(): void(): void(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function get(): void(): void(): object
    {
        $pendingRequest = HttpFacade::withOptions([
            ...config('seo.http.options', []),
            ...$this->options,
        ])->withHeaders([
            ...config('seo.http.headers', []),
            ...$this->headers,
        ]);

        return $pendingRequest->get($this->url);
    }

    public function getRemoteResponse(): void(): void(): object
    {
        $options = [
            'timeout' => 30,
            'return_transfer' => true,
            'follow_location' => true,
            'no_body' => true,
            'header' => true,
        ];

        if (app()->runningUnitTests()) {
            $options = [
                ...$options,
                'ssl_verifyhost' => false,
                'ssl_verifypeer' => false,
                'ssl_verifystatus' => false,
            ];
        }

        $domain = parse_url($this->url, PHP_URL_HOST);

        if (in_array($domain, array_keys(config('seo.resolve')))) {
            $port = str_contains($this->url, 'https://') ? 443 : 80;

            $ipAddress = config('seo.resolve')[$domain];

            if (! empty($ipAddress)) {
                $options = [
                    ...$options,
                    'resolve' => ["{$domain}:{$port}:{$ipAddress}"],
                ];
            }
        }

        $this->withOptions($options);

        return $this->get();
    }
}
