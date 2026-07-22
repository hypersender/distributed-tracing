<?php

use Hypersender\DistributedTracing\Tests\ContextVerificationJob;
use Hypersender\DistributedTracing\Tests\TestCase;
use Illuminate\Support\Facades\Context;

beforeEach(function () {
    Context::flush();
    ContextVerificationJob::$captured = [];
    config()->set('queue.default', 'sync');
});

afterEach(function () {
    Context::flush();
});

it('propagates s-request-id to queued jobs', function () {
    Context::add('s-request-id', 'job-req-123');

    dispatch(new ContextVerificationJob);

    expect(ContextVerificationJob::$captured['s-request-id'])->toBe('job-req-123');
});

it('propagates all instance context fields to queued jobs', function () {
    Context::add('s-request-id', 'job-req-456');
    Context::add('instance_id', 888);
    Context::add('instance_name', 'job-instance');
    Context::add('service_instance_id', 'WHATSAPP-888');

    dispatch(new ContextVerificationJob);

    expect(ContextVerificationJob::$captured)->toMatchArray([
        's-request-id' => 'job-req-456',
        'instance_id' => 888,
        'instance_name' => 'job-instance',
        'service_instance_id' => 'WHATSAPP-888',
    ]);
});

it('each dispatch captures Context at dispatch time, not handle time', function () {
    Context::add('s-request-id', 'first-dispatch');
    dispatch(new ContextVerificationJob);
    $firstCapture = ContextVerificationJob::$captured;

    ContextVerificationJob::$captured = [];
    Context::add('s-request-id', 'second-dispatch');
    dispatch(new ContextVerificationJob);
    $secondCapture = ContextVerificationJob::$captured;

    expect($firstCapture['s-request-id'])->toBe('first-dispatch');
    expect($secondCapture['s-request-id'])->toBe('second-dispatch');
});

it('jobs dispatched without Context have null tracing fields', function () {
    dispatch(new ContextVerificationJob);

    expect(ContextVerificationJob::$captured['s-request-id'])->toBeNull();
    expect(ContextVerificationJob::$captured['instance_id'])->toBeNull();
});
