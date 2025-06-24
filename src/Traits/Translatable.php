<?php

namespace Backstage\Seo\Traits;

trait Translatable
{
    public function getTranslatedDescription(): string
    {
        return __($this->description);
    }
}
