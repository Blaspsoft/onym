<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Strategies;

use Blaspsoft\Onym\Context;
use Blaspsoft\Onym\Contracts\Strategy;
use Illuminate\Support\Str;

class UuidStrategy implements Strategy
{
    public function part(Context $context, array $options): string
    {
        return (string) Str::uuid();
    }
}
