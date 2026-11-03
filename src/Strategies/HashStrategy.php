<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Strategies;

use Blaspsoft\Onym\Context;
use Blaspsoft\Onym\Contracts\Strategy;
use InvalidArgumentException;

class HashStrategy implements Strategy
{
    public function part(Context $context, array $options): string
    {
        $algorithm = (string) ($options['algorithm'] ?? 'md5');

        if (! in_array($algorithm, hash_algos(), true)) {
            throw new InvalidArgumentException("Invalid hash algorithm: {$algorithm}");
        }

        // Hash the name as given, so hashes match v1 for the same input.
        return hash($algorithm, $context->original);
    }
}
