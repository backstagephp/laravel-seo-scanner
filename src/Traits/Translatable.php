<?php

namespace Backstage\Seo\Traits;

trait Translatable
{
    public function getTranslatedDescription(): void(): string
    {
        return __($this->description);
    }
}
