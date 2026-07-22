<?php

use Hypersender\DistributedTracing\Logging\DistributedTracingProcessor;
use Hypersender\DistributedTracing\Tests\TestCase;
use Illuminate\Support\Facades\Context;
use Monolog\Level;
use Monolog\LogRecord;

beforeEach(function () {
    Context::flush();
});

afterEach(function () {
    Context::flush();
});

// ── injects s-request-id ────────────────────────────────

it('injects s-request-id from Context', function () {
    Context::add('s-request-id', 'req-123');

    $record = processRecord();

    expect($record->context)->toHaveKey('s-request-id', 'req-123');
});

it('injects all instance context fields', function () {
    Context::add('instance_id', 42);
    Context::add('instance_name', 'my-instance');
    Context::add('service_instance_id', 'WHATSAPP-42');

    $record = processRecord();

    expect($record->context['instance_id'])->toBe(42);
    expect($record->context['instance_name'])->toBe('my-instance');
    expect($record->context['service_instance_id'])->toBe('WHATSAPP-42');
});

it('preserves existing context', function () {
    Context::add('s-request-id', 'req-999');

    $record = processRecord(['existing' => 'value']);

    expect($record->context['existing'])->toBe('value');
    expect($record->context['s-request-id'])->toBe('req-999');
});

it('adds nothing when Context is empty', function () {
    $record = processRecord(['existing' => 'value']);

    expect($record->context)->toHaveCount(1);
    expect($record->context['existing'])->toBe('value');
});

it('injects s-request-id and instance context together', function () {
    Context::add('s-request-id', 'all-456');
    Context::add('instance_id', 10);
    Context::add('service_instance_id', 'SMS-10');

    $record = processRecord();

    expect($record->context)->toHaveKeys([
        's-request-id', 'instance_id', 'service_instance_id',
    ]);
    expect($record->context['s-request-id'])->toBe('all-456');
    expect($record->context['instance_id'])->toBe(10);
});

// ── all log levels ────────────────────────────────────────

it('works with all log levels', function (Level $level) {
    Context::add('s-request-id', 'level-test');

    $processor = new DistributedTracingProcessor;
    $record = new LogRecord(
        datetime: new DateTimeImmutable,
        channel: 'test',
        level: $level,
        message: 'level test',
        context: [],
    );

    $processed = $processor($record);

    expect($processed->context)->toHaveKey('s-request-id', 'level-test');
})->with([
    'debug' => Level::Debug,
    'info' => Level::Info,
    'warning' => Level::Warning,
    'error' => Level::Error,
    'critical' => Level::Critical,
]);

// ── helpers ───────────────────────────────────────────────

function processRecord(array $context = []): LogRecord
{
    $processor = new DistributedTracingProcessor;

    return $processor(new LogRecord(
        datetime: new DateTimeImmutable,
        channel: 'test',
        level: Level::Info,
        message: 'test',
        context: $context,
    ));
}
