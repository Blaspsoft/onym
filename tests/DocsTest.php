<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Tests;

use Blaspsoft\Onym\Context;
use Blaspsoft\Onym\Contracts\Strategy;
use Blaspsoft\Onym\Facades\Onym;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

/**
 * Checks that the examples in README.md and UPGRADE.md give the output they show.
 */
class DocsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('s3');
        Carbon::setTestNow('2024-03-15 14:30:05');
        Str::createRandomStringsUsing(fn (int $length) => substr('x7k2p9Qa81LmZr4T', 0, $length));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Str::createRandomStringsNormally();

        parent::tearDown();
    }

    #[Test]
    public function the_intro_examples(): void
    {
        Storage::put('invoices/report_1.pdf', '');
        Storage::put('invoices/report_2.pdf', '');

        $this->assertSame('q3-report.pdf', Onym::slug('Q3 Report', 'pdf'));
        $this->assertSame('2024-03-15_q3-report_x7k2p9.pdf', Onym::pipeline()->date()->slug()->random(6)->make('Q3 Report', 'pdf'));
        $this->assertSame('report_3.pdf', Onym::unique('report', 'pdf', 'numbered', directory: 'invoices'));
        $this->assertStringEndsWith('.png', Onym::fromUpload(UploadedFile::fake()->create('avatar.jpg', 1, 'image/png'), 'uuid'));
    }

    #[Test]
    public function the_usage_examples(): void
    {
        $this->assertSame('archive_q3-report.pdf', Onym::slug('Q3 Report', 'pdf', ['prefix' => 'archive_']));
        $this->assertSame('q3-report.pdf', Onym::make('Q3 Report', 'pdf', 'slug'));
        $this->assertSame('archive_q3-report.pdf', Onym::make(filename: 'Q3 Report', extension: 'pdf', strategy: 'slug', options: ['prefix' => 'archive_']));
        $this->assertSame('x7k2p9Qa81LmZr4T.txt', Onym::make());
    }

    #[Test]
    public function the_strategy_table(): void
    {
        $this->assertSame('x7k2p9Qa81LmZr4T.pdf', Onym::random('Q3 Report', 'pdf'));
        $this->assertTrue(Str::isUuid(pathinfo(Onym::uuid('Q3 Report', 'pdf'), PATHINFO_FILENAME)));
        $this->assertTrue(Str::isUlid(pathinfo(Onym::make('Q3 Report', 'pdf', 'ulid'), PATHINFO_FILENAME)));
        $this->assertSame('2024-03-15_14-30-05_Q3 Report.pdf', Onym::timestamp('Q3 Report', 'pdf'));
        $this->assertSame('2024-03-15_Q3 Report.pdf', Onym::date('Q3 Report', 'pdf'));
        $this->assertSame('Q3 Report_1.pdf', Onym::numbered('Q3 Report', 'pdf'));
        $this->assertSame('q3-report.pdf', Onym::slug('Q3 Report', 'pdf'));
        $this->assertSame('56337e9426f8db384e3ebda650723693.pdf', Onym::hash('Q3 Report', 'pdf'));
    }

    #[Test]
    public function the_pipeline_examples(): void
    {
        $this->assertSame(
            'inv_20240315_q3-report_x7k2p9.pdf',
            Onym::pipeline()->date('Ymd')->slug()->random(6)->prefix('inv_')->make('Q3 Report', 'pdf'),
        );

        $report = Onym::pipeline()->date()->slug();

        $this->assertSame('2024-03-15_q3-report.pdf', $report->make('Q3 Report', 'pdf'));
        $this->assertSame('2024-03-15_q3-report_2.pdf', $report->number(2)->make('Q3 Report', 'pdf'));
    }

    #[Test]
    public function the_custom_strategy_examples(): void
    {
        $invoice = new class implements Strategy
        {
            public function part(Context $context, array $options): string
            {
                return 'INV-'.str_pad((string) ($options['number'] ?? 1), 5, '0', STR_PAD_LEFT);
            }
        };

        Onym::extend('invoice', $invoice);
        $this->assertSame('INV-00042.pdf', Onym::make(null, 'pdf', 'invoice', ['number' => 42]));

        Onym::extend('invoice', fn ($app) => $invoice);
        $this->assertSame('20240315_INV-00001.pdf', Onym::pipeline()->date('Ymd')->add('invoice')->make(null, 'pdf'));

        Onym::extend('report', Onym::pipeline()->date('Ymd')->slug()->separator('-'));
        $this->assertSame('20240315-q3-report.pdf', Onym::make('Q3 Report', 'pdf', 'report'));
    }

    #[Test]
    public function the_unique_examples(): void
    {
        Storage::disk('s3')->put('q3-report.pdf', '');
        Storage::put('2024-03-15_q3-report.pdf', '');

        $this->assertSame('q3-report-2.pdf', Onym::unique('Q3 Report', 'pdf', 'slug', disk: 's3'));
        $this->assertSame('2024-03-15_q3-report_2.pdf', Onym::pipeline()->date()->slug()->unique('Q3 Report', 'pdf'));
    }

    #[Test]
    public function the_upload_examples(): void
    {
        Storage::put('documents/q3-report.pdf', '');

        $this->assertSame('q3-report.pdf', Onym::fromUpload(UploadedFile::fake()->create('Q3 Report.PDF', 1, 'application/pdf'), 'slug'));
        $this->assertSame('avatar_1.png', Onym::fromUpload(UploadedFile::fake()->create('avatar.php', 1, 'image/png'), 'numbered'));
        $this->assertSame('q3-report-2.pdf', Onym::fromUpload(UploadedFile::fake()->create('Q3 Report.pdf', 1, 'application/pdf'), 'slug', directory: 'documents'));
    }

    #[Test]
    public function the_safety_examples(): void
    {
        $this->assertSame('env_1.php', Onym::numbered('../../.env', 'php'));
        $this->assertSame('20240315_report.pdf', Onym::date('report', 'pdf', ['format' => 'Y/m/d']));
        $this->assertSame('My Report.PDF', Onym::sanitize('../../My Report.PDF'));
        $this->assertSame('env', Onym::sanitize('.env'));
    }

    #[Test]
    public function the_testing_example(): void
    {
        Onym::fake(['invoice.pdf', 'receipt.pdf']);

        Storage::disk('s3')->put('invoices/'.Onym::uuid(null, 'pdf'), '');

        Onym::assertGenerated('invoice.pdf');
        Storage::disk('s3')->assertExists('invoices/invoice.pdf');

        Onym::fake(fn ($filename, $extension, $steps) => "{$filename}.{$extension}");
        $this->assertSame('report.pdf', Onym::numbered('report', 'pdf'));
    }

    #[Test]
    public function the_upgrade_guide_examples(): void
    {
        $this->assertSame('invoicesreport_1.pdf', Onym::numbered('report', 'pdf', ['prefix' => 'invoices/']));
        $this->assertSame('20240315_report.pdf', Onym::date('report', 'pdf', ['format' => 'Y/m/d']));
        $this->assertSame(8, strlen(pathinfo(Onym::random(null, 'pdf', ['length' => 8]), PATHINFO_FILENAME)));

        config(['onym.default_separator' => '-']);
        $this->assertSame('report-1.pdf', app(\Blaspsoft\Onym\Onym::class)->numbered('report', 'pdf'));
    }
}
