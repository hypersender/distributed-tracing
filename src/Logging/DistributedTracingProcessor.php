<?php

namespace Hypersender\DistributedTracing\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class DistributedTracingProcessor implements ProcessorInterface
{
    /**
     * Enrich the log record with s-request-id and instance context
     * from Laravel's Context facade.
     *
     * NewrelicLogHandler reads from $record->context (not extra),
     * so we write to context to ensure fields reach New Relic.
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;

        $fields = ['s-request-id', 'instance_id', 'instance_name', 'service_instance_id'];

        foreach ($fields as $field) {
            $value = \Illuminate\Support\Facades\Context::get($field);

            if ($value !== null) {
                $context[$field] = $value;
            }
        }

        return $record->with(context: $context);
    }
}
