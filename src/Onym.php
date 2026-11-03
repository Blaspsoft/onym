<?php

declare(strict_types=1);

namespace Blaspsoft\Onym;

use Blaspsoft\Onym\Contracts\Strategy;
use Blaspsoft\Onym\Support\Sanitizer;
use Closure;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Carbon;

class Onym
{
    /**
     * Strategy names that are built from several pieces.
     *
     * Any other name is a single strategy registered with the manager.
     *
     * @var array<string, list<string>>
     */
    public const PRESETS = [
        'timestamp' => ['timestamp', 'name'],
        'date' => ['date', 'name'],
        'numbered' => ['name', 'number'],
    ];

    public function __construct(
        protected OnymManager $strategies,
        protected Repository $config,
        protected Sanitizer $sanitizer = new Sanitizer,
    ) {}

    /**
     * Generate a filename.
     *
     * @param  string|null  $filename  The original name (defaults to config "default_filename").
     * @param  string|null  $extension  The extension, with or without a leading dot (defaults to config "default_extension").
     * @param  string|null  $strategy  The strategy name (defaults to config "strategy").
     * @param  array<string, mixed>|null  $options  Merged over the config options for the strategy.
     */
    public function make(
        ?string $filename = null,
        ?string $extension = null,
        ?string $strategy = null,
        ?array $options = null,
    ): string {
        $strategy ??= $this->strategies->getDefaultDriver();
        $options = array_merge((array) $this->config->get("onym.options.{$strategy}", []), $options ?? []);

        $defaultFilename = (string) $this->config->get('onym.default_filename', 'file');
        $defaultExtension = (string) $this->config->get('onym.default_extension', 'txt');
        $original = $filename ?? $defaultFilename;
        $separator = (string) ($options['separator'] ?? $this->config->get('onym.default_separator', '_'));

        $context = new Context(
            original: $original,
            name: $this->sanitizer->filename($original, $defaultFilename),
            extension: $this->sanitizer->extension($extension ?? $defaultExtension, $defaultExtension),
            separator: $separator,
            fallbackName: $defaultFilename,
            now: Carbon::now(),
        );

        $parts = array_map(
            fn (string $step): string => $this->strategies->strategy($step)->part($context, $options),
            self::PRESETS[$strategy] ?? [$strategy],
        );

        return ($options['prefix'] ?? '')
            .implode($separator, $parts)
            .($options['suffix'] ?? '')
            .'.'.$context->extension;
    }

    /**
     * @param  array<string, mixed>|null  $options
     */
    public function random(?string $filename = null, ?string $extension = null, ?array $options = null): string
    {
        return $this->make($filename, $extension, 'random', $options);
    }

    /**
     * @param  array<string, mixed>|null  $options
     */
    public function uuid(?string $filename = null, ?string $extension = null, ?array $options = null): string
    {
        return $this->make($filename, $extension, 'uuid', $options);
    }

    /**
     * @param  array<string, mixed>|null  $options
     */
    public function timestamp(?string $filename = null, ?string $extension = null, ?array $options = null): string
    {
        return $this->make($filename, $extension, 'timestamp', $options);
    }

    /**
     * @param  array<string, mixed>|null  $options
     */
    public function date(?string $filename = null, ?string $extension = null, ?array $options = null): string
    {
        return $this->make($filename, $extension, 'date', $options);
    }

    /**
     * @param  array<string, mixed>|null  $options
     */
    public function numbered(?string $filename = null, ?string $extension = null, ?array $options = null): string
    {
        return $this->make($filename, $extension, 'numbered', $options);
    }

    /**
     * @param  array<string, mixed>|null  $options
     */
    public function slug(?string $filename = null, ?string $extension = null, ?array $options = null): string
    {
        return $this->make($filename, $extension, 'slug', $options);
    }

    /**
     * @param  array<string, mixed>|null  $options
     */
    public function hash(?string $filename = null, ?string $extension = null, ?array $options = null): string
    {
        return $this->make($filename, $extension, 'hash', $options);
    }

    /**
     * Register a custom strategy.
     *
     * Pass a Strategy instance, or a closure that receives the container and returns one.
     *
     * @param  Strategy|Closure(Container): Strategy  $strategy
     */
    public function extend(string $name, Strategy|Closure $strategy): static
    {
        $this->strategies->extend(
            $name,
            fn (Container $app): Strategy => $strategy instanceof Strategy ? $strategy : $strategy($app),
        );

        return $this;
    }

    /**
     * Whether a strategy name can be used with make().
     */
    public function hasStrategy(string $name): bool
    {
        return isset(self::PRESETS[$name]) || $this->strategies->has($name);
    }
}
