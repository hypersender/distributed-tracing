<?php

namespace Hypersender\DistributedTracing\Tests;

use Hypersender\DistributedTracing\Logging\DistributedTracingProcessor;
use Nagi\LaravelNewrelicLogApi\NewrelicLogHandler;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            \Hypersender\DistributedTracing\DistributedTracingServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('queue.default', 'sync');

        config()->set('logging.channels.newrelic-log-api', [
            'driver' => 'monolog',
            'handler' => NewrelicLogHandler::class,
            'level' => 'debug',
            'processors' => [
                DistributedTracingProcessor::class,
            ],
        ]);
    }
}
