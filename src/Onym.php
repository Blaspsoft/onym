<?php

declare(strict_types=1);

namespace Blaspsoft\Onym;

use Blaspsoft\Onym\Contracts\Strategy;
use Blaspsoft\Onym\Support\Sanitizer;
use Closure;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    /**
     * Strategies that give a new name on every call, so unique() simply tries again.
     *
     * @var list<string>
     */
    public const RANDOM_STRATEGIES = ['random', 'uuid', 'ulid'];

    /**
     * @param  (Closure(): FilesystemFactory)|null  $filesystems  Resolves storage disks for unique().
     */
    public function __construct(
        protected OnymManager $strategies,
        protected Repository $config,
        protected Sanitizer $sanitizer = new Sanitizer,
        protected ?Closure $filesystems = null,
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

        $steps = array_map(
            fn (string $step): array => [$step, $options],
            self::PRESETS[$strategy] ?? [$strategy],
        );

        return $this->generate($steps, $filename, $extension, $options);
    }

    /**
     * Generate a filename that does not exist yet on a storage disk.
     *
     * The first candidate is what make() would return. If it is taken:
     * "numbered" counts up (report_2, report_3), "random", "uuid" and "ulid"
     * generate a new name, and every other strategy appends a counter
     * (report-2, report-3).
     *
     * The check and your write are separate steps, so two requests at the same
     * moment can still pick the same name; use random, uuid or ulid where that matters.
     *
     * @param  array<string, mixed>|null  $options
     * @param  string|null  $disk  The disk to check (defaults to config "unique.disk", then the default disk).
     * @param  string  $directory  The directory on the disk the file will be stored in.
     */
    public function unique(
        ?string $filename = null,
        ?string $extension = null,
        ?string $strategy = null,
        ?array $options = null,
        ?string $disk = null,
        string $directory = '',
    ): string {
        $strategy ??= $this->strategies->getDefaultDriver();
        $options ??= [];
        $merged = array_merge((array) $this->config->get("onym.options.{$strategy}", []), $options);

        return $this->firstAvailable(function (int $attempt) use ($filename, $extension, $strategy, $options, $merged): string {
            if ($attempt === 1 || in_array($strategy, self::RANDOM_STRATEGIES, true)) {
                return $this->make($filename, $extension, $strategy, $options);
            }

            if ($strategy === 'numbered') {
                return $this->make($filename, $extension, $strategy, [
                    ...$options,
                    'number' => $this->countFrom($merged['number'] ?? 1, $attempt - 1),
                ]);
            }

            return $this->make($filename, $extension, $strategy, [
                ...$options,
                'counter' => $this->counter($merged, $attempt),
            ]);
        }, $disk, $directory);
    }

    /**
     * Generate a filename for an uploaded file.
     *
     * The original name (without its extension) is used as the filename, and
     * the extension comes from the file's detected MIME type rather than the
     * name the client sent, falling back to the client's extension. Pass a
     * disk or directory to make sure the name is not taken.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function fromUpload(
        UploadedFile $file,
        ?string $strategy = null,
        ?array $options = null,
        ?string $disk = null,
        ?string $directory = null,
    ): string {
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->guessExtension() ?: ($file->getClientOriginalExtension() ?: null);

        if ($disk === null && $directory === null) {
            return $this->make($filename, $extension, $strategy, $options);
        }

        return $this->unique($filename, $extension, $strategy, $options, $disk, $directory ?? '');
    }

    /**
     * Try candidates until one does not exist on the disk.
     *
     * @internal Use unique().
     *
     * @param  Closure(int): string  $candidate  Receives the attempt number, starting at 1.
     */
    public function firstAvailable(Closure $candidate, ?string $disk = null, string $directory = ''): string
    {
        if ($this->filesystems === null) {
            throw new RuntimeException('Onym::unique() needs Laravel\'s filesystem; resolve Onym from the container');
        }

        $storage = ($this->filesystems)()->disk($disk ?? $this->config->get('onym.unique.disk'));
        $attempts = $this->config->get('onym.unique.attempts', 100);
        $directory = trim($directory, '/');

        if (! is_int($attempts) || $attempts < 1) {
            throw new InvalidArgumentException('Onym config [unique.attempts] must be an integer of at least 1');
        }

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $name = $candidate($attempt);

            if (! $storage->exists($directory === '' ? $name : "{$directory}/{$name}")) {
                return $name;
            }
        }

        throw new RuntimeException("Onym could not find a free filename after {$attempts} attempts");
    }

    /**
     * The separator and attempt number appended to deterministic names by unique().
     *
     * @internal
     *
     * @param  array<string, mixed>  $options
     */
    public function counter(array $options, int $attempt): string
    {
        return $this->stringOption($options, 'separator', (string) $this->config->get('onym.default_separator', '_')).$attempt;
    }

    /**
     * Add to a number, keeping any zero-padding ("007" + 1 = "008").
     */
    protected function countFrom(mixed $start, int $add): string
    {
        if (! is_int($start) && ! (is_string($start) && ctype_digit($start))) {
            throw new InvalidArgumentException('The [number] option must be a whole number of 0 or more');
        }

        $next = (string) ((int) $start + $add);

        return is_string($start) ? str_pad($next, strlen($start), '0', STR_PAD_LEFT) : $next;
    }

    /**
     * Start an immutable pipeline that joins several strategies into one name.
     *
     * Onym::pipeline()->date()->slug()->random(6)->make('Q3 Report', 'pdf');
     */
    public function pipeline(): Pipeline
    {
        return new Pipeline($this);
    }

    /**
     * Generate a filename from a list of steps.
     *
     * @internal Use make() or pipeline().
     *
     * @param  list<array{0: string|Strategy, 1: array<string, mixed>}>  $steps
     * @param  array<string, mixed>  $options  Name-level options: prefix, suffix, separator, sanitize, and
     *                                         counter (added after the length cut; used by unique()).
     */
    public function generate(array $steps, ?string $filename, ?string $extension, array $options): string
    {
        if ($steps === []) {
            throw new InvalidArgumentException('An Onym pipeline needs at least one step');
        }

        $defaultFilename = (string) $this->config->get('onym.default_filename', 'file');
        $defaultExtension = (string) $this->config->get('onym.default_extension', 'txt');
        $original = $filename ?? $defaultFilename;
        $separator = $this->stringOption($options, 'separator', (string) $this->config->get('onym.default_separator', '_'));

        $context = new Context(
            original: $original,
            name: $this->sanitizer->filename($original, $defaultFilename),
            extension: $this->sanitizer->extension($extension ?? $defaultExtension, $defaultExtension),
            separator: $separator,
            fallbackName: $defaultFilename,
            now: Carbon::now(),
        );

        $basename = $this->stringOption($options, 'prefix')
            .implode($separator, $this->parts($steps, $context))
            .$this->stringOption($options, 'suffix');

        // The counter added by unique() goes on after the name is cut to length,
        // so a long name still gets a different counter on each attempt.
        $counter = $this->stringOption($options, 'counter');

        // Clean the whole name, so prefixes, suffixes, date formats and custom
        // strategies cannot add path separators or make the file hidden either.
        if ($options['sanitize'] ?? $this->config->get('onym.sanitize', true)) {
            $basename = $this->sanitizer->filename($basename, $defaultFilename);
            $counter = $this->sanitizer->filename($counter, '');
        }

        $basename = $this->sanitizer->limit($basename, $context->extension, $this->maxLength() - strlen($counter));

        return $basename.$counter.'.'.$context->extension;
    }

    /**
     * Run each step against the context. Each step's options are merged
     * over the config options for that strategy name.
     *
     * @internal
     *
     * @param  list<array{0: string|Strategy, 1: array<string, mixed>}>  $steps
     * @return list<string>
     */
    public function parts(array $steps, Context $context): array
    {
        return array_map(function (array $step) use ($context): string {
            [$strategy, $options] = $step;

            if ($strategy instanceof Strategy) {
                return $strategy->part($context, $options);
            }

            $options = array_merge((array) $this->config->get("onym.options.{$strategy}", []), $options);

            return $this->strategies->strategy($strategy)->part($context, $options);
        }, $steps);
    }

    /**
     * Make an existing filename safe to store: path separators, control
     * characters and leading dots are removed, the extension is cleaned and
     * the result is cut to the configured maximum length.
     *
     * sanitize('../../My Report.PDF') returns 'My Report.PDF'.
     */
    public function sanitize(string $filename): string
    {
        $defaultFilename = (string) $this->config->get('onym.default_filename', 'file');
        $cleaned = $this->sanitizer->filename($filename, $defaultFilename);
        $dot = strrpos($cleaned, '.');

        if ($dot === false || $dot === 0) {
            return $this->sanitizer->limit($cleaned, '', $this->maxLength());
        }

        $name = $this->sanitizer->filename(substr($cleaned, 0, $dot), $defaultFilename);
        $extension = $this->sanitizer->extension(substr($cleaned, $dot + 1), '');

        if ($extension === '') {
            return $this->sanitizer->limit($name, '', $this->maxLength());
        }

        return $this->sanitizer->limit($name, $extension, $this->maxLength()).'.'.$extension;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    protected function stringOption(array $options, string $key, string $default = ''): string
    {
        $value = $options[$key] ?? $default;

        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            throw new InvalidArgumentException("Onym option [{$key}] must be a string, ".get_debug_type($value).' given');
        }

        return (string) $value;
    }

    protected function maxLength(): int
    {
        $maxLength = $this->config->get('onym.max_length', 255);

        if (! is_int($maxLength) || $maxLength < 2) {
            throw new InvalidArgumentException('Onym config [max_length] must be an integer of at least 2');
        }

        return $maxLength;
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
