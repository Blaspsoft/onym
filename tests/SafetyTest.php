<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Tests;

use Blaspsoft\Onym\Context;
use Blaspsoft\Onym\Contracts\Strategy;
use Blaspsoft\Onym\Facades\Onym;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class SafetyTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function it_cleans_prefixes_and_suffixes(): void
    {
        $this->assertSame('a_1.txt', Onym::numbered('a', 'txt', ['prefix' => '../']));
        $this->assertSame('a_1etc.txt', Onym::numbered('a', 'txt', ['suffix' => '/etc']));
        $this->assertSame('hidden_1.txt', Onym::numbered('hidden', 'txt', ['prefix' => '.']));
    }

    #[Test]
    public function it_cleans_date_formats_that_contain_slashes(): void
    {
        Carbon::setTestNow('2024-03-15 14:30:00');

        $this->assertSame('20240315_report.pdf', Onym::date('report', 'pdf', ['format' => 'Y/m/d']));
    }

    #[Test]
    public function it_cleans_the_output_of_custom_strategies(): void
    {
        Onym::extend('unsafe', new class implements Strategy
        {
            public function part(Context $context, array $options): string
            {
                return "../../etc/\0passwd";
            }
        });

        $this->assertSame('etcpasswd.txt', Onym::make('x', 'txt', 'unsafe'));
    }

    #[Test]
    public function cleaning_can_be_turned_off_per_call(): void
    {
        Carbon::setTestNow('2024-03-15 14:30:00');

        $this->assertSame('2024/03/15_report.pdf', Onym::date('report', 'pdf', ['format' => 'Y/m/d', 'sanitize' => false]));
        $this->assertSame('invoices/a_1.txt', Onym::numbered('a', 'txt', ['prefix' => 'invoices/', 'sanitize' => false]));
    }

    #[Test]
    public function cleaning_can_be_turned_off_in_config(): void
    {
        config()->set('onym.sanitize', false);

        $this->assertSame('invoices/a_1.txt', Onym::numbered('a', 'txt', ['prefix' => 'invoices/']));
    }

    #[Test]
    public function the_input_filename_is_cleaned_even_when_sanitize_is_off(): void
    {
        $this->assertSame('env_1.php', Onym::numbered('../../.env', 'php', ['sanitize' => false]));
    }

    #[Test]
    public function it_keeps_names_within_the_maximum_length(): void
    {
        $name = Onym::slug(str_repeat('a', 300), 'pdf');

        $this->assertSame(255, strlen($name));
        $this->assertStringEndsWith('.pdf', $name);
    }

    #[Test]
    public function it_honours_a_configured_maximum_length(): void
    {
        config()->set('onym.max_length', 20);

        $this->assertSame('aaaaaaaaaaaaaaaa.pdf', Onym::slug(str_repeat('a', 50), 'pdf'));
        $this->assertSame('short.pdf', Onym::slug('short', 'pdf'));
    }

    #[Test]
    public function it_does_not_split_multibyte_characters_when_cutting(): void
    {
        config()->set('onym.max_length', 10);

        $name = Onym::numbered('ééééééééé', 'txt');

        $this->assertTrue(mb_check_encoding($name, 'UTF-8'));
        $this->assertLessThanOrEqual(10, strlen($name));
        $this->assertSame('ééé.txt', $name);
    }

    #[Test]
    public function it_rejects_an_extension_that_leaves_no_room(): void
    {
        config()->set('onym.max_length', 5);

        $this->expectException(InvalidArgumentException::class);

        Onym::slug('report', 'html');
    }

    #[Test]
    public function it_rejects_an_invalid_max_length_config(): void
    {
        config()->set('onym.max_length', '255');

        $this->expectException(InvalidArgumentException::class);

        Onym::slug('report', 'pdf');
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function sanitizeCases(): array
    {
        return [
            'traversal' => ['../../My Report.PDF', 'My Report.PDF'],
            'hidden file' => ['.env', 'env'],
            'windows path' => ['C:\\Users\\me\\file.txt', 'C:Usersmefile.txt'],
            'null byte' => ["evil\0.php", 'evil.php'],
            'double extension' => ['archive.tar.gz', 'archive.tar.gz'],
            'no extension' => ['README', 'README'],
            'trailing dot' => ['name.', 'name'],
            'nothing left' => ['../', 'file'],
            'bad extension characters' => ['photo.jp g', 'photo.jpg'],
        ];
    }

    #[Test]
    #[DataProvider('sanitizeCases')]
    public function sanitize_makes_an_existing_filename_safe(string $input, string $expected): void
    {
        $this->assertSame($expected, Onym::sanitize($input));
    }

    #[Test]
    public function sanitize_keeps_the_extension_when_cutting(): void
    {
        $name = Onym::sanitize(str_repeat('b', 400).'.docx');

        $this->assertSame(255, strlen($name));
        $this->assertStringEndsWith('.docx', $name);
    }

    /**
     * @return array<string, array{string, array<string, mixed>}>
     */
    public static function invalidOptions(): array
    {
        return [
            'random length zero' => ['random', ['length' => 0]],
            'random length text' => ['random', ['length' => 'long']],
            'number negative' => ['numbered', ['number' => -1]],
            'number text' => ['numbered', ['number' => 'two']],
            'empty date format' => ['date', ['format' => '']],
            'array timestamp format' => ['timestamp', ['format' => ['Y']]],
            'unknown hash algorithm' => ['hash', ['algorithm' => 'nope']],
            'array prefix' => ['slug', ['prefix' => ['a']]],
            'object suffix' => ['slug', ['suffix' => new \stdClass]],
            'boolean separator' => ['numbered', ['separator' => true]],
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     */
    #[Test]
    #[DataProvider('invalidOptions')]
    public function it_rejects_invalid_options(string $strategy, array $options): void
    {
        $this->expectException(InvalidArgumentException::class);

        Onym::make('report', 'pdf', $strategy, $options);
    }

    #[Test]
    public function numeric_strings_are_accepted_for_numbers(): void
    {
        $this->assertSame('report_7.pdf', Onym::numbered('report', 'pdf', ['number' => '7']));
        $this->assertSame(8, strlen(pathinfo(Onym::random(null, 'txt', ['length' => '8']), PATHINFO_FILENAME)));
    }
}
