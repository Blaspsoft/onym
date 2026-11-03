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

        if (filter_var($number, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
            throw new InvalidArgumentException('The [number] option must be a whole number of 0 or more');
        }

        return (string) $number;
    }
}
