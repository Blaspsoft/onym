<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string make(?string $filename = null, ?string $extension = null, ?string $strategy = null, ?array<string, mixed> $options = null)
 * @method static string random(?string $filename = null, ?string $extension = null, ?array<string, mixed> $options = null)
 * @method static string uuid(?string $filename = null, ?string $extension = null, ?array<string, mixed> $options = null)
 * @method static string timestamp(?string $filename = null, ?string $extension = null, ?array<string, mixed> $options = null)
 * @method static string date(?string $filename = null, ?string $extension = null, ?array<string, mixed> $options = null)
 * @method static string numbered(?string $filename = null, ?string $extension = null, ?array<string, mixed> $options = null)
 * @method static string slug(?string $filename = null, ?string $extension = null, ?array<string, mixed> $options = null)
 * @method static string hash(?string $filename = null, ?string $extension = null, ?array<string, mixed> $options = null)
 * @method static \Blaspsoft\Onym\Onym extend(string $name, \Blaspsoft\Onym\Contracts\Strategy|\Closure $strategy)
 * @method static bool hasStrategy(string $name)
 * @method static string sanitize(string $filename)
 * @method static \Blaspsoft\Onym\Pipeline pipeline()
 *
 * @see \Blaspsoft\Onym\Onym
 */
class Onym extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'onym';
    }
}
