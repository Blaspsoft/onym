<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Strategies;

use Blaspsoft\Onym\Context;
use Blaspsoft\Onym\Contracts\Strategy;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RandomStrategy implements Strategy
{
    public function part(Context $context, array $options): string
    {
        $length = (int) ($options['length'] ?? 16);

        if ($length < 1) {
            throw new InvalidArgumentException("Random length must be at least 1, {$length} given");
        }

        return Str::random($length);
    }
}
