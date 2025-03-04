<?php

namespace Blaspsoft\Onym\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string make(string $defaultFilename, ?string $strategy = null, ?string $extension = null, ?array $options = null)
 * @method static string random(string $extension, ?array $options = [])
 * @method static string uuid(string $extension, ?array $options = [])
 * @method static string timestamp(string $defaultFilename, string $extension, ?array $options = [])
 * @method static string date(string $defaultFilename, string $extension, ?array $options = [])
 * @method static string numbered(string $defaultFilename, string $extension, ?array $options = [])
 * @method static string slug(string $defaultFilename, string $extension, ?array $options = [])
 * @method static string hash(string $defaultFilename, string $extension, ?array $options = [])
 */
class Onym extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'onym';
    }
}
