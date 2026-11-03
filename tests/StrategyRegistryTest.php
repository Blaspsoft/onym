<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Tests;

use Blaspsoft\Onym\Context;
use Blaspsoft\Onym\Contracts\Strategy;
use Blaspsoft\Onym\Facades\Onym;
use Blaspsoft\Onym\OnymManager;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;

class StrategyRegistryTest extends TestCase
{
    #[Test]
    public function it_throws_for_an_unknown_strategy(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown Onym strategy [timestmap].');

        Onym::make('report', 'pdf', 'timestmap');
    }

    #[Test]
    public function it_registers_a_strategy_instance(): void
    {
        Onym::extend('invoice', new class implements Strategy
        {
            public function part(Context $context, array $options): string
            {
                return 'INV-'.str_pad((string) ($options['number'] ?? 1), 5, '0', STR_PAD_LEFT);
            }
        });

        $this->assertSame('INV-00042.pdf', Onym::make('ignored', 'pdf', 'invoice', ['number' => 42]));
        $this->assertTrue(Onym::hasStrategy('invoice'));
    }

    #[Test]
    public function it_registers_a_strategy_from_a_closure_that_receives_the_container(): void
    {
        $this->app->instance('tenant', 'acme');

        Onym::extend('tenant', static fn (Container $app) => new class((string) $app->make('tenant')) implements Strategy
        {
            public function __construct(private string $tenant) {}

            public function part(Context $context, array $options): string
            {
                return $this->tenant.'-'.$context->name;
            }
        });

        $this->assertSame('pre_acme-report.csv', Onym::make('report', 'csv', 'tenant', ['prefix' => 'pre_']));
    }

    #[Test]
    public function extending_replaces_a_strategy_that_was_already_used(): void
    {
        $this->assertStringEndsWith('.txt', Onym::random(null, 'txt'));

        Onym::extend('random', new class implements Strategy
        {
            public function part(Context $context, array $options): string
            {
                return 'not-random';
            }
        });

        $this->assertSame('not-random.txt', Onym::random(null, 'txt'));
    }

    #[Test]
    public function custom_strategies_get_the_cleaned_name_and_extension(): void
    {
        Onym::extend('echo', new class implements Strategy
        {
            public function part(Context $context, array $options): string
            {
                return $context->name.'|'.$context->extension;
            }
        });

        $this->assertSame('env|pdf.pdf', Onym::make('../.env', '.pdf', 'echo'));
    }

    #[Test]
    public function it_reports_which_strategies_exist(): void
    {
        foreach (['random', 'uuid', 'timestamp', 'date', 'numbered', 'slug', 'hash', 'name', 'number'] as $name) {
            $this->assertTrue(Onym::hasStrategy($name), $name);
        }

        $this->assertFalse(Onym::hasStrategy('nope'));
    }

    #[Test]
    public function the_manager_rejects_drivers_that_are_not_strategies(): void
    {
        $manager = $this->app->make(OnymManager::class);
        $manager->extend('broken', fn () => new \stdClass);

        $this->expectException(InvalidArgumentException::class);

        $manager->strategy('broken');
    }

    #[Test]
    public function the_onym_class_is_a_container_singleton(): void
    {
        $this->assertSame($this->app->make('onym'), $this->app->make(\Blaspsoft\Onym\Onym::class));
    }
}
