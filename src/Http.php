<?php

namespace Backstage\Seo;

use Illuminate\Support\Facades\Http as HttpFacade;

class Http
{
    public string $url;

    public array $options = [];

    public array $headers = [];

    public HttpFacade $http;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public static function make(string $url): self
    {
        return new self($url);
    }

    public function withOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function get(): object
    {
        $http = HttpFacade::withOptions([
            ...config('seo.http.options', []),
            ...$this->options,
        ])->withHeaders([
            ...config('seo.http.headers', []),
            ...$this->headers,
        ]);

        return $http->get($this->url);
    }

    public function getRemoteResponse(): object
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
