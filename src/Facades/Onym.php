<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Facades;

use Blaspsoft\Onym\Onym as OnymService;
use Blaspsoft\Onym\Testing\OnymFake;
use Closure;
use Illuminate\Support\Facades\Facade;
use RuntimeException;

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
 * @method static string unique(?string $filename = null, ?string $extension = null, ?string $strategy = null, ?array<string, mixed> $options = null, ?string $disk = null, string $directory = '')
 * @method static string fromUpload(\Symfony\Component\HttpFoundation\File\UploadedFile $file, ?string $strategy = null, ?array<string, mixed> $options = null, ?string $disk = null, ?string $directory = null)
 * @method static list<string> generated()
 * @method static void assertGenerated(string|\Closure $name, ?int $times = null)
 * @method static void assertNotGenerated(string|\Closure $name)
 * @method static void assertGeneratedCount(int $count)
 * @method static void assertNothingGenerated()
 *
 * @see OnymService
 * @see OnymFake
 */
class Onym extends Facade
{
    /**
     * Replace Onym with a fake that returns the given names in order and
     * records every name generated.
     *
     * @param  array<array-key, string>|(Closure(?string, ?string, list<string>): string)  $names
     */
    public static function fake(array|Closure $names = []): OnymFake
    {
        $root = static::getFacadeRoot();

        if (! $root instanceof OnymService) {
            throw new RuntimeException('Onym could not be resolved from the container to fake');
        }

        // Faking again starts a fresh fake with new names and an empty record.
        static::swap($fake = new OnymFake($root, $names));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return 'onym';
    }
}
