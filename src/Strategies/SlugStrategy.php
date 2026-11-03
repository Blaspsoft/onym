<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Strategies;

use Blaspsoft\Onym\Context;
use Blaspsoft\Onym\Contracts\Strategy;
use Illuminate\Support\Str;

class SlugStrategy implements Strategy
{
    public function part(Context $context, array $options): string
    {
        $separator = $context->separator;

        // Names with nothing to slug (e.g. "!!!" or non-Latin text) fall back to the default filename.
        return Str::slug($context->original, $separator)
            ?: Str::slug($context->fallbackName, $separator)
            ?: 'file';
    }
}
