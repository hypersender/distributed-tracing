<?php

namespace Hypersender\DistributedTracing\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            \Hypersender\DistributedTracing\DistributedTracingServiceProvider::class,
        ];
    }
}
