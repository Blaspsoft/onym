<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Strategies;

use Blaspsoft\Onym\Context;
use Blaspsoft\Onym\Contracts\Strategy;
use InvalidArgumentException;

class DateStrategy implements Strategy
{
    public function part(Context $context, array $options): string
    {
        $format = $options['format'] ?? 'Y-m-d';

        if (! is_string($format) || $format === '') {
            throw new InvalidArgumentException('The [format] option must be a non-empty date format string');
        }

        return $context->now->format($format);
    }
}
