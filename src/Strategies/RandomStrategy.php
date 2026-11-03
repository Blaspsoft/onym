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
        $length = filter_var($options['length'] ?? 16, FILTER_VALIDATE_INT);

        if ($length === false || $length < 1) {
            throw new InvalidArgumentException('Random length must be a whole number of at least 1');
        }

        return Str::random($length);
    }
}
