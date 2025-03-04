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
        $filename = $this->onym->random(8, 'txt');
        
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
    public function it_generates_prefix_filenames()
    {
        $filename = $this->onym->prefix('test', 'txt', ['prefix' => 'pre_']);
        
        $this->assertEquals('pre_test.txt', $filename);
    }

    #[Test]
    public function it_throws_exception_for_missing_prefix()
    {
        $this->expectException(InvalidArgumentException::class);
        
        $this->onym->prefix('test', 'txt', []);
    }

    #[Test]
    public function it_generates_suffix_filenames()
    {
        $filename = $this->onym->suffix('test', 'txt', ['suffix' => '_suffix']);
        
        $this->assertEquals('test_suffix.txt', $filename);
    }

    #[Test]
    public function it_throws_exception_for_missing_suffix()
    {
        $this->expectException(InvalidArgumentException::class);
        
        $this->onym->suffix('test', 'txt', []);
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
        $filename = $this->onym->make('test', 'txt', 'uuid');
        
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.txt$/', $filename);
    }

    #[Test]
    public function it_allows_options_override_in_make_method()
    {
        $filename = $this->onym->make('test', 'txt', 'prefix', ['prefix' => 'custom_']);
        
        $this->assertEquals('custom_test.txt', $filename);
    }
}