<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Testing;

use Blaspsoft\Onym\Contracts\Strategy;
use Blaspsoft\Onym\Onym;
use Closure;
use Illuminate\Support\Testing\Fakes\Fake;
use PHPUnit\Framework\Assert as PHPUnit;

/**
 * Stands in for Onym in tests: returns the names you give it, in order, and
 * records every name generated so you can assert on them.
 *
 *     Onym::fake(['invoice.pdf', 'receipt.pdf']);
 *     Onym::fake(fn (?string $filename, ?string $extension) => "{$filename}.{$extension}");
 *
 * Once queued names run out (or with no names at all) it generates real
 * names, which are still recorded.
 */
class OnymFake extends Onym implements Fake
{
    /** @var list<string> */
    private array $queue;

    /** @var (Closure(?string, ?string, list<string>): string)|null */
    private ?Closure $using;

    /** @var list<string> */
    private array $generated = [];

    /**
     * @param  array<array-key, string>|(Closure(?string, ?string, list<string>): string)  $names
     */
    public function __construct(Onym $onym, array|Closure $names = [])
    {
        parent::__construct($onym->strategies, $onym->config, $onym->sanitizer, $onym->filesystems);

        $this->queue = $names instanceof Closure ? [] : array_values($names);
        $this->using = $names instanceof Closure ? $names : null;
    }

    /**
     * @param  list<array{0: string|Strategy, 1: array<string, mixed>}>  $steps
     * @param  array<string, mixed>  $options
     */
    public function generate(array $steps, ?string $filename, ?string $extension, array $options): string
    {
        if ($this->queue !== []) {
            $name = array_shift($this->queue);
        } elseif ($this->using !== null) {
            $stepNames = array_map(fn (array $step): string => is_string($step[0]) ? $step[0] : $step[0]::class, $steps);
            $name = ($this->using)($filename, $extension, $stepNames);
        } else {
            $name = parent::generate($steps, $filename, $extension, $options);
        }

        return $this->generated[] = $name;
    }

    /**
     * Every name generated so far, in order.
     *
     * @return list<string>
     */
    public function generated(): array
    {
        return $this->generated;
    }

    /**
     * Assert a name was generated, optionally an exact number of times.
     *
     * @param  string|(Closure(string): bool)  $name  The name, or a callback that receives each name.
     */
    public function assertGenerated(string|Closure $name, ?int $times = null): void
    {
        $count = count($this->matching($name));
        $label = is_string($name) ? "[{$name}]" : 'matching the callback';

        if ($times === null) {
            PHPUnit::assertGreaterThan(0, $count, "No filename {$label} was generated.");

            return;
        }

        PHPUnit::assertSame($times, $count, "A filename {$label} was generated {$count} times instead of {$times} times.");
    }

    /**
     * @param  string|(Closure(string): bool)  $name  The name, or a callback that receives each name.
     */
    public function assertNotGenerated(string|Closure $name): void
    {
        $label = is_string($name) ? "[{$name}]" : 'matching the callback';

        PHPUnit::assertSame([], $this->matching($name), "An unexpected filename {$label} was generated.");
    }

    public function assertGeneratedCount(int $count): void
    {
        $actual = count($this->generated);

        PHPUnit::assertSame($count, $actual, "{$actual} filenames were generated instead of {$count}.");
    }

    public function assertNothingGenerated(): void
    {
        PHPUnit::assertSame([], $this->generated, 'Filenames were generated unexpectedly.');
    }

    /**
     * @param  string|(Closure(string): bool)  $name
     * @return list<string>
     */
    private function matching(string|Closure $name): array
    {
        return array_values(array_filter(
            $this->generated,
            fn (string $generated): bool => is_string($name) ? $generated === $name : (bool) $name($generated),
        ));
    }
}
