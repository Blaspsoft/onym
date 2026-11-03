<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Tests;

use Blaspsoft\Onym\Facades\Onym;
use Blaspsoft\Onym\Onym as OnymService;
use Blaspsoft\Onym\Testing\OnymFake;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;

class FakeTest extends TestCase
{
    #[Test]
    public function it_returns_queued_names_in_order(): void
    {
        Onym::fake(['invoice.pdf', 'receipt.pdf']);

        $this->assertSame('invoice.pdf', Onym::uuid(null, 'pdf'));
        $this->assertSame('receipt.pdf', Onym::make('anything', 'txt', 'slug'));
    }

    #[Test]
    public function it_generates_real_names_once_the_queue_is_empty(): void
    {
        Onym::fake(['first.txt']);

        $this->assertSame('first.txt', Onym::slug('My File', 'txt'));
        $this->assertSame('my-file.txt', Onym::slug('My File', 'txt'));
    }

    #[Test]
    public function it_can_name_files_with_a_callback(): void
    {
        Onym::fake(fn (?string $filename, ?string $extension, array $steps) => implode('+', $steps).":{$filename}.{$extension}");

        $this->assertSame('name+number:report.pdf', Onym::numbered('report', 'pdf'));
        $this->assertSame('date+slug:Q3.csv', Onym::pipeline()->date()->slug()->make('Q3', 'csv'));
    }

    #[Test]
    public function it_records_every_generated_name(): void
    {
        $fake = Onym::fake(['a.txt']);

        Onym::random(null, 'txt');
        Onym::slug('B', 'txt');

        $this->assertSame(['a.txt', 'b.txt'], $fake->generated());
        Onym::assertGenerated('a.txt');
        Onym::assertGenerated(fn (string $name) => str_starts_with($name, 'b'), 1);
        Onym::assertNotGenerated('c.txt');
        Onym::assertGeneratedCount(2);
    }

    #[Test]
    public function assert_nothing_generated_passes_when_nothing_was_generated(): void
    {
        Onym::fake();

        Onym::assertNothingGenerated();
    }

    #[Test]
    public function assertions_fail_with_a_clear_message(): void
    {
        Onym::fake(['a.txt']);
        Onym::make();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No filename [b.txt] was generated.');

        Onym::assertGenerated('b.txt');
    }

    #[Test]
    public function assert_generated_checks_the_number_of_times(): void
    {
        Onym::fake(['a.txt', 'a.txt']);
        Onym::make();
        Onym::make();

        Onym::assertGenerated('a.txt', 2);

        $this->expectException(AssertionFailedError::class);
        Onym::assertGenerated('a.txt', 1);
    }

    #[Test]
    public function assert_nothing_generated_fails_after_a_name_was_generated(): void
    {
        Onym::fake();
        Onym::make();

        $this->expectException(AssertionFailedError::class);
        Onym::assertNothingGenerated();
    }

    #[Test]
    public function the_fake_is_used_by_the_container_and_by_unique_and_uploads(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('taken.pdf', '');

        Onym::fake(['taken.pdf', 'free.pdf', 'upload.png']);

        $this->assertInstanceOf(OnymFake::class, $this->app->make(OnymService::class));
        $this->assertSame('free.pdf', Onym::unique('x', 'pdf', 'slug'));
        $this->assertSame('upload.png', Onym::fromUpload(UploadedFile::fake()->create('a.png', 1, 'image/png')));
        Onym::assertGeneratedCount(3);
    }

    #[Test]
    public function pipelines_built_before_faking_use_the_fake(): void
    {
        $pipeline = Onym::pipeline()->date()->slug();

        Onym::fake(['faked.pdf']);

        $this->assertSame('faked.pdf', $pipeline->make('Q3 Report', 'pdf'));
        Onym::assertGenerated('faked.pdf');
    }

    #[Test]
    public function faking_again_starts_fresh(): void
    {
        Onym::fake(['one.txt']);
        Onym::make();

        Onym::fake(['two.txt']);

        Onym::assertNothingGenerated();
        $this->assertSame('two.txt', Onym::make());
    }

    #[Test]
    public function the_facade_reports_that_it_is_faked(): void
    {
        $this->assertFalse(Onym::isFake());

        Onym::fake();

        $this->assertTrue(Onym::isFake());
    }
}
