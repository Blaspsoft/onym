<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Strategies;

use Blaspsoft\Onym\Context;
use Blaspsoft\Onym\Contracts\Strategy;
use InvalidArgumentException;

class NumberStrategy implements Strategy
{
    public function part(Context $context, array $options): string
    {
        $number = $options['number'] ?? 1;

        // Digit strings are kept as given, so zero-padding like "007" survives.
        $valid = (is_int($number) && $number >= 0) || (is_string($number) && ctype_digit($number));

        if (! $valid) {
            throw new InvalidArgumentException('The [number] option must be a whole number of 0 or more');
        }

        return (string) $number;
    }
}
