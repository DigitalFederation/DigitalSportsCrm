<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

it('skips the scheduler heartbeat when no url is configured', function () {
    Config::set('services.scheduler_heartbeat.url', null);
    Http::fake();

    $this->artisan('scheduler:heartbeat')
        ->expectsOutput('Scheduler heartbeat URL is not configured.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertNothingSent();
});

it('pings the configured scheduler heartbeat url', function () {
    Config::set('services.scheduler_heartbeat.url', 'https://example.com/heartbeat');
    Http::fake([
        'https://example.com/heartbeat' => Http::response('', 200),
    ]);

    $this->artisan('scheduler:heartbeat')
        ->expectsOutput('Scheduler heartbeat pinged successfully.')
        ->assertExitCode(Command::SUCCESS);

    Http::assertSent(fn ($request) => $request->url() === 'https://example.com/heartbeat');
});

it('fails when the scheduler heartbeat endpoint fails', function () {
    Config::set('services.scheduler_heartbeat.url', 'https://example.com/heartbeat');
    Http::fake([
        'https://example.com/heartbeat' => Http::response('', 500),
    ]);

    $this->artisan('scheduler:heartbeat')
        ->expectsOutput('Scheduler heartbeat ping failed.')
        ->assertExitCode(Command::FAILURE);
});
