<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Strategies;

use Blaspsoft\Onym\Context;
use Blaspsoft\Onym\Contracts\Strategy;
use Illuminate\Support\Str;

/**
 * A ULID: unique like a UUID, but sorts by creation time.
 */
class UlidStrategy implements Strategy
{
    public function part(Context $context, array $options): string
    {
        $ulid = (string) Str::ulid();

        return ($options['lowercase'] ?? false) ? strtolower($ulid) : $ulid;
    }
}
