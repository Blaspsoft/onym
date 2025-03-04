<?php

namespace Blaspsoft\Onym\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Blaspsoft\Onym\OnymServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            OnymServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('onym.strategy', 'random');
        config()->set('onym.options', []);
        config()->set('onym.default_filename', 'file');
        config()->set('onym.default_extension', 'txt');
    }
}