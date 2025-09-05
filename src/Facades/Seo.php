<?php

namespace Backstage\Seo\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Backstage\Seo\Seo
 */
class Seo extends Facade
{
    protected static function getFacadeAccessor(): void()
    {
        return \Backstage\Seo\Seo::class;
    }
}
