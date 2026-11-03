<?php

declare(strict_types=1);

namespace Blaspsoft\Onym;

use Blaspsoft\Onym\Contracts\Strategy;
use Blaspsoft\Onym\Facades\Onym as OnymFacade;
use InvalidArgumentException;

/**
 * Joins several strategies into one filename.
 *
 * Every method returns a new pipeline, so a base pipeline can be shared and
 * extended without affecting other uses of it:
 *
 *     $base = Onym::pipeline()->date()->slug();
 *     $base->random(6)->make('Q3 Report', 'pdf');   // 2024-03-15_q3-report_x7k2p9.pdf
 *     $base->number(2)->make('Q3 Report', 'pdf');   // 2024-03-15_q3-report_2.pdf
 *
 * A pipeline is also a Strategy, so it can be registered under a name:
 *
 *     Onym::extend('report', Onym::pipeline()->date()->slug());
 *     Onym::make('Q3 Report', 'pdf', 'report');
 */
final class Pipeline implements Strategy
{
    /** @var list<array{0: string|Strategy, 1: array<string, mixed>}> */
    private array $steps = [];

    /** @var array<string, mixed> */
    private array $options = [];

    /** Set while part() runs, to catch a pipeline that contains itself. */
    private bool $resolving = false;

    public function __construct(private readonly Onym $onym) {}

    /**
     * Add any registered strategy, or a Strategy instance, as the next piece.
     *
     * @param  array<string, mixed>  $options
     */
    public function add(string|Strategy $strategy, array $options = []): self
    {
        $pipeline = clone $this;
        $pipeline->steps[] = [$strategy, $options];

        return $pipeline;
    }

    /**
     * The original filename, cleaned.
     */
    public function name(): self
    {
        return $this->add('name');
    }

    /**
     * @param  int|array<string, mixed>  $options  The length, or an options array.
     */
    public function random(int|array $options = []): self
    {
        return $this->add('random', is_int($options) ? ['length' => $options] : $options);
    }

    public function uuid(): self
    {
        return $this->add('uuid');
    }

    /**
     * @param  string|array<string, mixed>  $options  The date format, or an options array.
     */
    public function timestamp(string|array $options = []): self
    {
        return $this->add('timestamp', is_string($options) ? ['format' => $options] : $options);
    }

    /**
     * @param  string|array<string, mixed>  $options  The date format, or an options array.
     */
    public function date(string|array $options = []): self
    {
        return $this->add('date', is_string($options) ? ['format' => $options] : $options);
    }

    /**
     * @param  int|array<string, mixed>  $options  The number, or an options array.
     */
    public function number(int|array $options = []): self
    {
        return $this->add('number', is_int($options) ? ['number' => $options] : $options);
    }

    /**
     * The original filename as a slug.
     *
     * @param  string|array<string, mixed>  $options  The word separator, or an options array.
     */
    public function slug(string|array $options = []): self
    {
        return $this->add('slug', is_string($options) ? ['separator' => $options] : $options);
    }

    /**
     * A hash of the original filename.
     *
     * @param  string|array<string, mixed>  $options  The algorithm, or an options array.
     */
    public function hash(string|array $options = []): self
    {
        return $this->add('hash', is_string($options) ? ['algorithm' => $options] : $options);
    }

    /**
     * The string placed between pieces (defaults to config "default_separator").
     */
    public function separator(string $separator): self
    {
        return $this->with('separator', $separator);
    }

    public function prefix(string $prefix): self
    {
        return $this->with('prefix', $prefix);
    }

    public function suffix(string $suffix): self
    {
        return $this->with('suffix', $suffix);
    }

    /**
     * Whether to clean the whole generated name (defaults to config "sanitize").
     */
    public function sanitize(bool $sanitize = true): self
    {
        return $this->with('sanitize', $sanitize);
    }

    /**
     * Generate the filename.
     */
    public function make(?string $filename = null, ?string $extension = null): string
    {
        return $this->onym()->generate($this->steps, $filename, $extension, $this->options);
    }

    /**
     * Generate a filename that does not exist yet on a storage disk.
     *
     * Pipelines with a random, uuid or ulid step simply try again; others
     * append a counter (2024-03-15_q3-report_2.pdf).
     */
    public function unique(?string $filename = null, ?string $extension = null, ?string $disk = null, string $directory = ''): string
    {
        $regenerates = (bool) array_filter(
            $this->steps,
            fn (array $step): bool => is_string($step[0]) && in_array($step[0], Onym::RANDOM_STRATEGIES, true),
        );

        return $this->onym()->firstAvailable(function (int $attempt) use ($filename, $extension, $regenerates): string {
            if ($attempt === 1 || $regenerates) {
                return $this->make($filename, $extension);
            }

            return $this->with('counter', $this->onym->counter($this->options, $attempt))->make($filename, $extension);
        }, $disk, $directory);
    }

    /**
     * Used when the pipeline is registered as a strategy: returns the joined
     * pieces, leaving the prefix, suffix and extension to the caller.
     */
    public function part(Context $context, array $options): string
    {
        if ($this->resolving) {
            throw new InvalidArgumentException('An Onym pipeline cannot contain itself');
        }

        $separator = (string) ($this->options['separator'] ?? $options['separator'] ?? $context->separator);
        $this->resolving = true;

        try {
            return implode($separator, $this->onym->parts($this->steps, $context));
        } finally {
            $this->resolving = false;
        }
    }

    /**
     * @return list<array{0: string|Strategy, 1: array<string, mixed>}>
     */
    public function steps(): array
    {
        return $this->steps;
    }

    /**
     * The Onym instance to generate with. A pipeline built before Onym::fake()
     * still goes through the fake, so its names are queued and recorded.
     */
    private function onym(): Onym
    {
        $root = OnymFacade::isFake() ? OnymFacade::getFacadeRoot() : null;

        return $root instanceof Onym ? $root : $this->onym;
    }

    private function with(string $key, mixed $value): self
    {
        $pipeline = clone $this;
        $pipeline->options[$key] = $value;

        return $pipeline;
    }
}
