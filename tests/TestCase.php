<?php

namespace Gardi\McpLaravel\Tests;

use Gardi\McpLaravel\McpServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [McpServiceProvider::class];
    }
}
