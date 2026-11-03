<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Tests;

use Blaspsoft\Onym\Context;
use Blaspsoft\Onym\Contracts\Strategy;
use Blaspsoft\Onym\Facades\Onym;
use Blaspsoft\Onym\Pipeline;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;

class PipelineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-03-15 14:30:05');
        Str::createRandomStringsUsing(fn (int $length) => substr(str_repeat('x7k2p9', 50), 0, $length));
        Str::createUuidsUsing(fn () => Uuid::fromString('123e4567-e89b-42d3-a456-426614174000'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Str::createRandomStringsNormally();
        Str::createUuidsNormally();

        parent::tearDown();
    }

    #[Test]
    public function it_joins_several_strategies(): void
    {
        $this->assertSame(
            '2024-03-15_q3-report_x7k2p9.pdf',
            Onym::pipeline()->date()->slug()->random(6)->make('Q3 Report', 'pdf'),
        );
    }

    #[Test]
    public function each_step_takes_its_own_options(): void
    {
        $this->assertSame(
            '20240315-Q3 Report-007-'.hash('sha1', 'Q3 Report').'.csv',
            Onym::pipeline()
                ->date('Ymd')
                ->name()
                ->add('number', ['number' => '007'])
                ->hash('sha1')
                ->separator('-')
                ->make('Q3 Report', 'csv'),
        );
    }

    #[Test]
    public function shortcut_arguments_are_the_main_option(): void
    {
        $this->assertSame(
            'q3.report_14:30_x7k2_3.txt',
            Onym::pipeline()->slug('.')->timestamp('H:i')->random(4)->number(3)->sanitize(false)->make('Q3 Report', 'txt'),
        );
    }

    #[Test]
    public function steps_use_config_defaults_for_their_strategy(): void
    {
        config()->set('onym.options.random.length', 3);
        config()->set('onym.options.date.format', 'd.m.Y');

        $this->assertSame('15.03.2024_x7k.txt', Onym::pipeline()->date()->random()->make());
    }

    #[Test]
    public function the_slug_word_separator_and_the_piece_separator_are_separate(): void
    {
        $this->assertSame('my-file__2024-03-15.txt', Onym::pipeline()->slug()->date()->separator('__')->make('My File', 'txt'));
        $this->assertSame('my_file-2024-03-15.txt', Onym::pipeline()->slug('_')->date()->separator('-')->make('My File', 'txt'));
    }

    #[Test]
    public function it_adds_prefix_suffix_and_extension_once(): void
    {
        $this->assertSame(
            'inv_123e4567-e89b-42d3-a456-426614174000_2024-03-15_final.pdf',
            Onym::pipeline()->uuid()->date()->prefix('inv_')->suffix('_final')->make('ignored', '.pdf'),
        );
    }

    #[Test]
    public function every_method_returns_a_new_pipeline(): void
    {
        $base = Onym::pipeline()->date()->slug();

        $withRandom = $base->random(6);
        $withNumber = $base->number(2)->separator('-');

        $this->assertNotSame($base, $withRandom);
        $this->assertCount(2, $base->steps());
        $this->assertSame('2024-03-15_q3-report.pdf', $base->make('Q3 Report', 'pdf'));
        $this->assertSame('2024-03-15_q3-report_x7k2p9.pdf', $withRandom->make('Q3 Report', 'pdf'));
        $this->assertSame('2024-03-15-q3-report-2.pdf', $withNumber->make('Q3 Report', 'pdf'));
    }

    #[Test]
    public function all_pieces_share_the_same_moment(): void
    {
        $advanceClock = new class implements Strategy
        {
            public function part(Context $context, array $options): string
            {
                Carbon::setTestNow(Carbon::now()->addSeconds(5));

                return 'between';
            }
        };

        $name = Onym::pipeline()->timestamp('His.u')->add($advanceClock)->timestamp('His.u')->sanitize(false)->make('x', 'txt');
        [$first, , $second] = explode('_', pathinfo($name, PATHINFO_FILENAME));

        $this->assertSame($first, $second);
    }

    #[Test]
    public function it_accepts_custom_strategies_by_name_or_instance(): void
    {
        Onym::extend('tenant', new class implements Strategy
        {
            public function part(Context $context, array $options): string
            {
                return 'acme';
            }
        });

        $upper = new class implements Strategy
        {
            public function part(Context $context, array $options): string
            {
                return strtoupper($context->name);
            }
        };

        $this->assertSame('acme_REPORT.txt', Onym::pipeline()->add('tenant')->add($upper)->make('report', 'txt'));
    }

    #[Test]
    public function a_pipeline_can_be_registered_as_a_named_strategy(): void
    {
        Onym::extend('report', Onym::pipeline()->date('Ymd')->slug()->separator('-'));

        $this->assertSame('archive_20240315-q3-report.pdf', Onym::make('Q3 Report', 'pdf', 'report', ['prefix' => 'archive_']));
        $this->assertSame('20240315-q3-report_7.pdf', Onym::pipeline()->add('report')->number(7)->make('Q3 Report', 'pdf'));
    }

    #[Test]
    public function the_whole_name_is_cleaned(): void
    {
        $this->assertSame('20240315_env.txt', Onym::pipeline()->date('Y/m/d')->name()->prefix('../')->separator('_')->make('../.env', 'txt'));
    }

    #[Test]
    public function a_named_pipeline_uses_the_callers_separator_when_it_has_none(): void
    {
        Onym::extend('report', Onym::pipeline()->date('Ymd')->slug());

        $this->assertSame('20240315-q3-report.pdf', Onym::make('Q3 Report', 'pdf', 'report', ['separator' => '-']));
        $this->assertSame('20240315.q3-report_7.pdf', Onym::pipeline()->add('report', ['separator' => '.'])->number(7)->make('Q3 Report', 'pdf'));
    }

    #[Test]
    public function a_pipeline_that_contains_itself_throws(): void
    {
        Onym::extend('loop', fn () => Onym::pipeline()->add('loop'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot contain itself');

        Onym::make('x', 'txt', 'loop');
    }

    #[Test]
    public function an_invalid_step_separator_throws(): void
    {
        Onym::extend('report', Onym::pipeline()->date()->slug());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Onym option [separator] must be a string, bool given');

        Onym::pipeline()->add('report', ['separator' => true])->make('Q3 Report', 'pdf');
    }

    #[Test]
    public function an_empty_pipeline_used_as_a_strategy_throws(): void
    {
        Onym::extend('empty', Onym::pipeline());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at least one step');

        Onym::pipeline()->date()->add('empty')->make('x', 'pdf');
    }

    #[Test]
    public function an_empty_pipeline_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at least one step');

        Onym::pipeline()->prefix('x')->make('report', 'pdf');
    }

    #[Test]
    public function an_unknown_step_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown Onym strategy [nope].');

        Onym::pipeline()->add('nope')->make();
    }

    #[Test]
    public function invalid_step_options_throw(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Onym::pipeline()->random(0)->make();
    }

    #[Test]
    public function pipeline_returns_a_pipeline(): void
    {
        $this->assertInstanceOf(Pipeline::class, Onym::pipeline());
    }
}
