<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Tests;

use Blaspsoft\Onym\Facades\Onym;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;

/**
 * Checks that v2 produces the same filenames as v1.1.2.
 *
 * tests/fixtures/v1-parity.json was recorded by running each call below
 * against v1.1.2 (with its own argument order) with time, random strings
 * and UUIDs frozen to the same values used here.
 */
class ParityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-03-15 14:30:05');
        Str::createRandomStringsUsing(fn (int $length) => substr(str_repeat('Ab3', 100), 0, $length));
        Str::createUuidsUsing(fn () => Uuid::fromString('123e4567-e89b-42d3-a456-426614174000'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Str::createRandomStringsNormally();
        Str::createUuidsNormally();

        parent::tearDown();
    }

    /**
     * @return array<string, array{Closure(): string}>
     */
    public static function calls(): array
    {
        $calls = [
            'make: defaults' => fn () => Onym::make(),
            'make: readme timestamp' => fn () => Onym::make('document', 'pdf', 'timestamp', ['format' => 'Y-m-d_H-i-s', 'prefix' => 'log_', 'suffix' => '_archive']),
            'make: readme date' => fn () => Onym::make('document', 'pdf', 'date', ['format' => 'Y-m-d', 'prefix' => 'dated_', 'suffix' => '_version']),
            'make: readme numbered' => fn () => Onym::make('document', 'pdf', 'numbered', ['number' => 5, 'prefix' => 'rev_', 'suffix' => '_final']),
            'make: readme slug' => fn () => Onym::make('My Document Name', 'pdf', 'slug', ['prefix' => 'post_', 'suffix' => '_draft']),
            'make: readme hash' => fn () => Onym::make('document', 'pdf', 'hash', ['algorithm' => 'md5', 'prefix' => 'hash_', 'suffix' => '_checksum']),
            'make: random length' => fn () => Onym::make(null, 'pdf', 'random', ['length' => 8]),
            'make: uuid' => fn () => Onym::make(null, 'pdf', 'uuid'),
            'make: doxswap named args' => fn () => Onym::make(strategy: 'timestamp', extension: 'pdf', options: ['prefix' => 'conv_']),
            'make: filename only' => fn () => Onym::make('Doc'),
            'timestamp: default' => fn () => Onym::timestamp('invoice', 'pdf'),
            'timestamp: separator and format' => fn () => Onym::timestamp('invoice', 'pdf', ['separator' => '-', 'format' => 'YmdHis']),
            'date: format' => fn () => Onym::date('report', 'csv', ['format' => 'Ymd']),
            'numbered: default' => fn () => Onym::numbered('file', 'txt'),
            'numbered: number and separator' => fn () => Onym::numbered('file', 'txt', ['number' => 3, 'separator' => '-']),
            'slug: default' => fn () => Onym::slug('Hello World!', 'md'),
            'slug: separator' => fn () => Onym::slug('Hello World', 'md', ['separator' => '_']),
            'slug: empty falls back' => fn () => Onym::slug('!!!', 'pdf'),
            'hash: sha256' => fn () => Onym::hash('document', 'txt', ['algorithm' => 'sha256']),
            'hash: uncleaned input' => fn () => Onym::hash('../a b', 'txt'),
            'random: default' => fn () => Onym::random(null, 'txt'),
            'random: length and prefix' => fn () => Onym::random(null, 'txt', ['length' => 4, 'prefix' => 'tmp_']),
            'uuid: suffix' => fn () => Onym::uuid(null, 'jpg', ['suffix' => '_x']),
            'clean: traversal name' => fn () => Onym::numbered('../../.env', 'php'),
            'clean: dotted extension' => fn () => Onym::slug('report', '.pdf'),
            'clean: traversal extension' => fn () => Onym::uuid(null, 'txt/../../shell.php'),
            'clean: double extension' => fn () => Onym::slug('report', 'tar.gz'),
            'clean: empty extension' => fn () => Onym::slug('report', ''),
        ];

        return array_map(fn (Closure $call) => [$call], $calls);
    }

    #[Test]
    #[DataProvider('calls')]
    public function it_matches_v1_output(Closure $call): void
    {
        $label = $this->dataName();
        $expected = $this->fixtures()[$label] ?? null;

        $this->assertNotNull($expected, "No v1 fixture recorded for [{$label}]");
        $this->assertSame($expected, $call());
    }

    #[Test]
    public function every_fixture_has_a_v2_call(): void
    {
        $this->assertSame(array_keys($this->fixtures()), array_keys(self::calls()));
    }

    /**
     * @return array<string, string>
     */
    private function fixtures(): array
    {
        return json_decode((string) file_get_contents(__DIR__.'/fixtures/v1-parity.json'), true, flags: JSON_THROW_ON_ERROR);
    }
}
