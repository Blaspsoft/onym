<?php

namespace Blaspsoft\Onym\Tests;

use Blaspsoft\Onym\Onym;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;

class SafeFilenameTest extends TestCase
{
    protected Onym $onym;

    protected function setUp(): void
    {
        parent::setUp();
        $this->onym = $this->app->make(Onym::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function it_strips_path_traversal_from_the_filename()
    {
        $this->assertEquals('env_1.php', $this->onym->make('../../.env', 'php', 'numbered'));
        $this->assertEquals('etcpasswd_1.txt', $this->onym->numbered('/etc/passwd', 'txt'));
        $this->assertEquals('ab_1.txt', $this->onym->numbered('a\\b', 'txt'));
    }

    #[Test]
    public function it_strips_control_characters_and_leading_dots_from_the_filename()
    {
        $this->assertEquals('ab_1.txt', $this->onym->numbered("a\0b", 'txt'));
        $this->assertEquals('htaccess_1.txt', $this->onym->numbered('.htaccess', 'txt'));
    }

    #[Test]
    public function it_falls_back_to_the_default_filename_when_nothing_is_left()
    {
        $this->assertEquals('file_1.txt', $this->onym->numbered('../', 'txt'));
        $this->assertEquals('file_1.pdf', $this->onym->make('..', 'pdf', 'numbered'));
    }

    #[Test]
    public function it_keeps_ordinary_filenames_unchanged()
    {
        $this->assertEquals('My Report v2.final_1.pdf', $this->onym->numbered('My Report v2.final', 'pdf'));
        $this->assertEquals('über-datei_1.txt', $this->onym->numbered('über-datei', 'txt'));
    }

    #[Test]
    public function it_cleans_the_extension()
    {
        $this->assertEquals('report.pdf', $this->onym->slug('report', '.pdf'));
        $this->assertEquals('report.tar.gz', $this->onym->slug('report', 'tar.gz'));
        $this->assertEquals('report.PDF', $this->onym->slug('report', 'PDF'));
        $this->assertEquals('report.txt', $this->onym->slug('report', ''));
        $this->assertStringEndsWith('.txt.shell.php', $this->onym->uuid(null, 'txt/../../shell.php'));
        $this->assertStringNotContainsString('/', $this->onym->uuid(null, 'txt/../../shell.php'));
    }

    #[Test]
    public function it_falls_back_to_the_default_filename_when_the_slug_is_empty()
    {
        $this->assertEquals('file.pdf', $this->onym->slug('!!!', 'pdf'));
        $this->assertEquals('pre_file.pdf', $this->onym->slug('???', 'pdf', ['prefix' => 'pre_']));
    }

    #[Test]
    public function it_rejects_a_random_length_below_one()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->onym->random(null, 'txt', ['length' => 0]);
    }

    #[Test]
    public function it_uses_carbon_so_time_can_be_frozen_in_tests()
    {
        Carbon::setTestNow('2024-03-15 14:30:00');

        $this->assertEquals('2024-03-15_report.pdf', $this->onym->date('report', 'pdf'));
        $this->assertEquals('2024-03-15_14-30-00_report.pdf', $this->onym->timestamp('report', 'pdf'));
    }

    #[Test]
    public function the_readme_examples_produce_the_documented_results()
    {
        Carbon::setTestNow('2024-03-15 14:30:00');

        $this->assertEquals('log_2024-03-15_14-30-00_document_archive.pdf', $this->onym->make('document', 'pdf', 'timestamp', [
            'format' => 'Y-m-d_H-i-s', 'prefix' => 'log_', 'suffix' => '_archive',
        ]));
        $this->assertEquals('dated_2024-03-15_document_version.pdf', $this->onym->make('document', 'pdf', 'date', [
            'format' => 'Y-m-d', 'prefix' => 'dated_', 'suffix' => '_version',
        ]));
        $this->assertEquals('rev_document_5_final.pdf', $this->onym->make('document', 'pdf', 'numbered', [
            'number' => 5, 'prefix' => 'rev_', 'suffix' => '_final',
        ]));
        $this->assertEquals('post_my-document-name_draft.pdf', $this->onym->make('My Document Name', 'pdf', 'slug', [
            'prefix' => 'post_', 'suffix' => '_draft',
        ]));
        $this->assertEquals('hash_'.md5('document').'_checksum.pdf', $this->onym->make('document', 'pdf', 'hash', [
            'algorithm' => 'md5', 'prefix' => 'hash_', 'suffix' => '_checksum',
        ]));
    }
}
