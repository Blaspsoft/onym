<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Tests;

use Blaspsoft\Onym\Facades\Onym;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class UlidTest extends TestCase
{
    #[Test]
    public function it_generates_a_ulid_filename(): void
    {
        $name = Onym::make('ignored', 'pdf', 'ulid');

        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}\.pdf$/', $name);
        $this->assertTrue(Str::isUlid(pathinfo($name, PATHINFO_FILENAME)));
    }

    #[Test]
    public function ulids_sort_by_creation_time(): void
    {
        $first = Onym::make(null, 'txt', 'ulid');
        usleep(2000);
        $second = Onym::make(null, 'txt', 'ulid');

        $this->assertLessThan(0, strcmp($first, $second));
    }

    #[Test]
    public function ulids_can_be_lowercase(): void
    {
        $name = Onym::make(null, 'txt', 'ulid', ['lowercase' => true]);

        $this->assertSame(strtolower($name), $name);
    }

    #[Test]
    public function random_names_can_be_lowercase(): void
    {
        Str::createRandomStringsUsing(fn (int $length) => substr(str_repeat('AbC', 10), 0, $length));

        $this->assertSame('abcabc.txt', Onym::random(null, 'txt', ['length' => 6, 'lowercase' => true]));
        $this->assertSame('AbCAbC.txt', Onym::random(null, 'txt', ['length' => 6]));

        Str::createRandomStringsNormally();
    }

    #[Test]
    public function the_pipeline_can_add_a_ulid_step(): void
    {
        $name = Onym::pipeline()->slug()->add('ulid', ['lowercase' => true])->make('My File', 'txt');

        $this->assertMatchesRegularExpression('/^my-file_[0-9a-z]{26}\.txt$/', $name);
    }

    #[Test]
    public function ulid_is_a_known_strategy(): void
    {
        $this->assertTrue(Onym::hasStrategy('ulid'));
    }
}
