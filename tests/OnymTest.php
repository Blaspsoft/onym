<?php

namespace Blaspsoft\Onym\Tests;

use DateTime;
use InvalidArgumentException;
use Blaspsoft\Onym\Onym;
use PHPUnit\Framework\Attributes\Test;

class OnymTest extends TestCase
{
    protected Onym $onym;

    protected function setUp(): void
    {
        parent::setUp();
        $this->onym = new Onym();
    }

    #[Test]
    public function it_uses_config_defaults()
    {
        $filename = $this->onym->make();
        $this->assertStringEndsWith('.txt', $filename);
    }

    #[Test]
    public function it_generates_random_filenames()
    {
        $filename = $this->onym->random('txt', ['length' => 8]);
        $this->assertEquals(12, strlen($filename)); // 8 chars + '.txt'
        $this->assertStringEndsWith('.txt', $filename);
    }

    #[Test]
    public function it_generates_uuid_filenames()
    {
        $filename = $this->onym->uuid('txt');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.txt$/', $filename);
    }

    #[Test]
    public function it_generates_timestamp_filenames()
    {
        $now = new DateTime();
        $filename = $this->onym->timestamp('test', 'txt', ['format' => 'Y-m-d_H-i-s']);
        $this->assertStringStartsWith($now->format('Y-m-d'), $filename);
        $this->assertStringEndsWith('test.txt', $filename);
    }

    #[Test]
    public function it_generates_date_filenames()
    {
        $now = new DateTime();
        $filename = $this->onym->date('test', 'txt', ['format' => 'Y-m-d']);
        $this->assertEquals($now->format('Y-m-d') . '_test.txt', $filename);
    }

    #[Test]
    public function it_generates_numbered_filenames()
    {
        $filename = $this->onym->numbered('test', 'txt', ['number' => 5]); 
        $this->assertEquals('test_5.txt', $filename);
    }

    #[Test]
    public function it_generates_slug_filenames()
    {
        $filename = $this->onym->slug('Test File Name', 'txt');    
        $this->assertEquals('test-file-name.txt', $filename);
    }

    #[Test]
    public function it_generates_hash_filenames()
    {
        $filename = $this->onym->hash('test', 'txt', ['algorithm' => 'md5']);
        $this->assertEquals(md5('test') . '.txt', $filename);
    }

    #[Test]
    public function it_throws_exception_for_invalid_hash_algorithm()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->onym->hash('test', 'txt', ['algorithm' => 'invalid']);
    }

    #[Test]
    public function it_allows_strategy_override_in_make_method()
    {
        $filename = $this->onym->make('test', 'uuid', 'txt');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.txt$/', $filename);
    }

    #[Test]
    public function it_allows_options_override_in_make_method()
    {
        $filename = $this->onym->make('file', 'random', 'txt', ['length' => 8, 'prefix' => 'custom_']);
        $this->assertEquals(19, strlen($filename));
        $this->assertStringEndsWith('.txt', $filename);
        $this->assertEquals('custom_', substr($filename, 0, 7));
    }

    #[Test]
    public function it_allows_options_override_in_random_method()
    {
        $filename = $this->onym->random('txt', ['length' => 8, 'prefix' => 'custom_']);
        $this->assertEquals(19, strlen($filename));
        $this->assertStringEndsWith('.txt', $filename);
        $this->assertEquals('custom_', substr($filename, 0, 7));
    }

    #[Test]
    public function it_allows_options_override_in_uuid_method()
    {
        $filename = $this->onym->uuid('txt', ['prefix' => 'custom_']);
        $this->assertMatchesRegularExpression('/^custom_[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.txt$/', $filename);
    }

    #[Test]
    public function it_allows_options_override_in_timestamp_method()
    {
        $filename = $this->onym->timestamp('test', 'txt', ['prefix' => 'custom_', 'format' => 'Y-m-d_H-i-s', 'suffix' => '_suffix']);
        $this->assertMatchesRegularExpression('/^custom_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}_test_suffix\.txt$/', $filename);
    }

    #[Test]
    public function it_allows_options_override_in_date_method()
    {
        $filename = $this->onym->date('test', 'txt', ['prefix' => 'custom_', 'format' => 'Y-m-d', 'suffix' => '_suffix']);
        $this->assertMatchesRegularExpression('/^custom_\d{4}-\d{2}-\d{2}_test_suffix\.txt$/', $filename);
    }

    #[Test]
    public function it_allows_options_override_in_numbered_method()
    {
        $filename = $this->onym->numbered('filename', 'txt', ['prefix' => 'prefix_', 'number' => 5, 'suffix' => '_suffix']);
        $this->assertEquals('prefix_filename_5_suffix.txt', $filename);
    }

    #[Test]
    public function it_allows_options_override_in_slug_method()
    {
        $filename = $this->onym->slug('filename', 'txt', ['prefix' => 'prefix_', 'suffix' => '_suffix']);        
        $this->assertEquals('prefix_filename_suffix.txt', $filename);
    }

    #[Test]
    public function it_allows_options_override_in_hash_method()
    {
        $hash = md5('filename');
        $filename = $this->onym->hash('filename', 'txt', ['prefix' => 'prefix_', 'suffix' => '_suffix', 'algorithm' => 'md5']);

        $this->assertEquals('prefix_' . $hash . '_suffix.txt', $filename);
    }

    #[Test]
    public function it_allows_options_override_in_make_method_with_null_values()
    {
        $filename = $this->onym->make('filename', 'random', 'txt', ['prefix' => null, 'suffix' => null, 'length' => null]);
        $this->assertEquals(20, strlen($filename));
        $this->assertStringEndsWith('.txt', $filename);
    }

    #[Test]
    public function it_allows_options_override_in_random_method_with_null_values()
    {
        $filename = $this->onym->random('txt', ['prefix' => null, 'suffix' => null, 'length' => null]);
        $this->assertEquals(20, strlen($filename));
    }

    #[Test]
    public function it_allows_options_override_in_uuid_method_with_null_values()
    {
        $filename = $this->onym->uuid('txt', ['prefix' => null, 'suffix' => null]);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.txt$/', $filename);
    }

    #[Test]
    public function it_allows_options_override_in_timestamp_method_with_null_values()
    {
        $filename = $this->onym->timestamp('test', 'txt', ['prefix' => null, 'suffix' => null, 'format' => null]);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}_test\.txt$/', $filename);
    }

    #[Test]
    public function it_allows_options_override_in_date_method_with_null_values()
    {
        $filename = $this->onym->date('test', 'txt', ['prefix' => null, 'suffix' => null, 'format' => null]);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}_test\.txt$/', $filename);
    }

    #[Test]
    public function it_allows_options_override_in_numbered_method_with_null_values()
    {
        $filename = $this->onym->numbered('filename', 'txt', ['prefix' => null, 'suffix' => null, 'number' => null]);
        $this->assertEquals('filename_1.txt', $filename);
    }

    #[Test]
    public function it_allows_options_override_in_slug_method_with_null_values()
    {
        $filename = $this->onym->slug('filename', 'txt', ['prefix' => null, 'suffix' => null]);
        $this->assertEquals('filename.txt', $filename);
    }

    #[Test]
    public function it_allows_options_override_in_hash_method_with_null_values()
    {
        $hash = md5('filename');
        $filename = $this->onym->hash('filename', 'txt', ['prefix' => null, 'suffix' => null, 'algorithm' => null]);
        $this->assertEquals($hash . '.txt', $filename);
    }
}