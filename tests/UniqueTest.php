<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Tests;

use Blaspsoft\Onym\Facades\Onym;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class UniqueTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('s3');
        Carbon::setTestNow('2024-03-15 14:30:05');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Str::createRandomStringsNormally();

        parent::tearDown();
    }

    #[Test]
    public function it_returns_the_normal_name_when_it_is_free(): void
    {
        $this->assertSame('report_1.pdf', Onym::unique('report', 'pdf', 'numbered'));
    }

    #[Test]
    public function numbered_counts_up_from_the_starting_number(): void
    {
        Storage::disk('local')->put('report_1.pdf', '');
        Storage::disk('local')->put('report_2.pdf', '');

        $this->assertSame('report_3.pdf', Onym::unique('report', 'pdf', 'numbered'));
        $this->assertSame('report_5.pdf', Onym::unique('report', 'pdf', 'numbered', ['number' => 5]));
    }

    #[Test]
    public function numbered_keeps_zero_padding_when_counting(): void
    {
        Storage::disk('local')->put('report_009.pdf', '');

        $this->assertSame('report_010.pdf', Onym::unique('report', 'pdf', 'numbered', ['number' => '009']));
    }

    #[Test]
    public function other_strategies_append_a_counter(): void
    {
        Storage::disk('local')->put('q3-report.pdf', '');
        Storage::disk('local')->put('q3-report-2.pdf', '');

        $this->assertSame('q3-report-3.pdf', Onym::unique('Q3 Report', 'pdf', 'slug'));
    }

    #[Test]
    public function long_names_keep_room_for_the_counter(): void
    {
        config()->set('onym.max_length', 20);
        Storage::disk('local')->put('aaaaaaaaaaaaaaaa.pdf', '');

        $this->assertSame('aaaaaaaaaaaaaa-2.pdf', Onym::unique(str_repeat('a', 40), 'pdf', 'slug'));
        $this->assertSame('aaaaaaaaaaaaaaaa.pdf', Onym::slug(str_repeat('a', 40), 'pdf'));
    }

    #[Test]
    public function long_names_keep_room_for_a_multi_digit_counter(): void
    {
        config()->set('onym.max_length', 20);
        Storage::disk('local')->put('aaaaaaaaaaaaaaaa.pdf', '');

        foreach (range(2, 9) as $attempt) {
            Storage::disk('local')->put("aaaaaaaaaaaaaa-{$attempt}.pdf", '');
        }

        $this->assertSame('aaaaaaaaaaaaa-10.pdf', Onym::unique(str_repeat('a', 40), 'pdf', 'slug'));
    }

    #[Test]
    public function long_pipeline_names_keep_room_for_the_counter(): void
    {
        config()->set('onym.max_length', 20);
        Storage::disk('local')->put('aaaaaaaaaaaaaaaa.pdf', '');

        $this->assertSame('aaaaaaaaaaaaaa_2.pdf', Onym::pipeline()->name()->unique(str_repeat('a', 40), 'pdf'));
    }

    #[Test]
    public function the_counter_goes_after_an_existing_suffix(): void
    {
        Storage::disk('local')->put('2024-03-15_report_final.pdf', '');

        $this->assertSame('2024-03-15_report_final_2.pdf', Onym::unique('report', 'pdf', 'date', ['suffix' => '_final']));
    }

    #[Test]
    public function random_strategies_try_a_new_name(): void
    {
        $names = ['aaaa', 'bbbb', 'cccc'];
        Str::createRandomStringsUsing(function () use (&$names) {
            return array_shift($names);
        });

        Storage::disk('local')->put('aaaa.txt', '');
        Storage::disk('local')->put('bbbb.txt', '');

        $this->assertSame('cccc.txt', Onym::unique(null, 'txt', 'random', ['length' => 4]));
    }

    #[Test]
    public function it_checks_inside_the_given_directory(): void
    {
        Storage::disk('local')->put('invoices/report_1.pdf', '');

        $this->assertSame('report_2.pdf', Onym::unique('report', 'pdf', 'numbered', directory: 'invoices/'));
        $this->assertSame('report_1.pdf', Onym::unique('report', 'pdf', 'numbered', directory: 'receipts'));
    }

    #[Test]
    public function it_checks_the_given_disk(): void
    {
        Storage::disk('s3')->put('report_1.pdf', '');

        $this->assertSame('report_1.pdf', Onym::unique('report', 'pdf', 'numbered'));
        $this->assertSame('report_2.pdf', Onym::unique('report', 'pdf', 'numbered', disk: 's3'));
    }

    #[Test]
    public function it_uses_the_configured_disk(): void
    {
        config()->set('onym.unique.disk', 's3');
        Storage::disk('s3')->put('report_1.pdf', '');

        $this->assertSame('report_2.pdf', Onym::unique('report', 'pdf', 'numbered'));
    }

    #[Test]
    public function it_gives_up_after_the_configured_attempts(): void
    {
        config()->set('onym.unique.attempts', 2);
        Storage::disk('local')->put('report.pdf', '');
        Storage::disk('local')->put('report-2.pdf', '');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('after 2 attempts');

        Onym::unique('report', 'pdf', 'slug');
    }

    #[Test]
    public function it_rejects_an_invalid_attempts_config(): void
    {
        config()->set('onym.unique.attempts', 0);

        $this->expectException(InvalidArgumentException::class);

        Onym::unique('report', 'pdf', 'slug');
    }

    #[Test]
    public function pipelines_append_a_counter(): void
    {
        Storage::disk('local')->put('2024-03-15_q3-report.pdf', '');

        $this->assertSame('2024-03-15_q3-report_2.pdf', Onym::pipeline()->date()->slug()->unique('Q3 Report', 'pdf'));
    }

    #[Test]
    public function pipelines_with_a_random_step_try_a_new_name(): void
    {
        $names = ['aaaa', 'bbbb'];
        Str::createRandomStringsUsing(function () use (&$names) {
            return array_shift($names);
        });

        Storage::disk('local')->put('q3-report_aaaa.pdf', '');

        $this->assertSame('q3-report_bbbb.pdf', Onym::pipeline()->slug()->random(4)->unique('Q3 Report', 'pdf'));
    }

    #[Test]
    public function from_upload_uses_the_original_name_and_the_detected_extension(): void
    {
        $file = UploadedFile::fake()->create('Q3 Report.PDF', 10, 'application/pdf');

        $this->assertSame('q3-report.pdf', Onym::fromUpload($file, 'slug'));
    }

    #[Test]
    public function from_upload_ignores_a_misleading_client_extension(): void
    {
        $file = UploadedFile::fake()->create('avatar.php', 10, 'image/png');

        $this->assertSame('avatar_1.png', Onym::fromUpload($file, 'numbered'));
    }

    #[Test]
    public function from_upload_cleans_the_client_name(): void
    {
        $file = UploadedFile::fake()->create('../../.env.txt', 1, 'text/plain');

        $this->assertSame('env_1.txt', Onym::fromUpload($file, 'numbered'));
    }

    #[Test]
    public function from_upload_can_make_sure_the_name_is_free(): void
    {
        Storage::disk('local')->put('docs/q3-report.pdf', '');
        $file = UploadedFile::fake()->create('Q3 Report.pdf', 10, 'application/pdf');

        $this->assertSame('q3-report-2.pdf', Onym::fromUpload($file, 'slug', directory: 'docs'));
    }
}
