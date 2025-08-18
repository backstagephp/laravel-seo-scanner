<?php

namespace Backstage\Seo\Traits;

use Closure;

trait PerformCheck
{
    public ?string $url = null;

    public bool $useJavascript = false;

    /**
     * The current Eloquent model associated with the scan, if any.
     */
    public mixed $model = null;

    public function __invoke(array $data, Closure $next)
    {
        $this->url = $data['url'] ?? null;

        $this->useJavascript = $data['javascriptResponse'] ?? false;

        // Provide model context when available
        $this->model = $data['model'] ?? null;

        if (! in_array('exit', $data)) {
            $result = $this->check($data['response'], $data['crawler']);
        }

        $result = $result ?? false;

        $data = $this->setResult($data, $result);

        if (! $result && ! $this->continueAfterFailure) {
            $data['exit'] = true;
        }

        // Advance the progress bar.
        if (isset($data['progress'])) {
            $data['progress']->advance();
        }

        return $next($data);
    }

    public function setResult(array $data, bool $result): array
    {
        if (in_array('exit', $data)) {
            unset($data['checks'][__CLASS__]);

            return $data;
        }

        $value = ['result' => $result];

        if (! $result) {
            $value['failureReason'] = $this->failureReason ?? null;
            $value['expectedValue'] = $this->expectedValue ?? null;
            $value['actualValue'] = $this->actualValue ?? null;
        }

        $data['checks'][__CLASS__] = $value;

        return $data;
    }

    /**
     * Replace the properties of the class with the values of the array.
     */
    public function merge(array $result): self
    {
        array_walk($result, function ($value, $key) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        });

        return $this;
    }
}
