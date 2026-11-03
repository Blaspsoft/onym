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
        $separator = (string) ($options['separator'] ?? $context->separator);

        // Names with nothing to slug (e.g. "!!!" or non-Latin text) fall back to the default filename.
        // Compare with '' rather than using ?:, so a slug of "0" is kept.
        foreach ([$context->original, $context->fallbackName] as $name) {
            $slug = Str::slug($name, $separator);

            if ($slug !== '') {
                return $slug;
            }
        }

        return 'file';
    }
}
