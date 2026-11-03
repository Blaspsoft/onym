<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Strategies;

use Blaspsoft\Onym\Context;
use Blaspsoft\Onym\Contracts\Strategy;
use InvalidArgumentException;

class TimestampStrategy implements Strategy
{
    public function part(Context $context, array $options): string
    {
        $format = $options['format'] ?? 'Y-m-d_H-i-s';

        if (! is_string($format) || $format === '') {
            throw new InvalidArgumentException('The [format] option must be a non-empty date format string');
        }

        return $context->now->format($format);
    }
}
