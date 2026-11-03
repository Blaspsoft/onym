<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Strategies;

use Blaspsoft\Onym\Context;
use Blaspsoft\Onym\Contracts\Strategy;

class DateStrategy implements Strategy
{
    public function part(Context $context, array $options): string
    {
        return $context->now->format((string) ($options['format'] ?? 'Y-m-d'));
    }
}
