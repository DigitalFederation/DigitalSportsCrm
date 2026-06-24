<?php

use App\Jobs\GenerateReportJob;
use App\Livewire\Reports\ReportGenerator;
use App\Models\User;
use Domain\Reports\Models\GeneratedReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake([GenerateReportJob::class]);
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('filters with start and end dates are saved to generated report', function () {
    Livewire::test(ReportGenerator::class)
        ->set('reportType', 'EntitiesListReport')
        ->set('startDate', '2025-01-01')
        ->set('endDate', '2025-12-31')
        ->call('generateReport');

    $report = GeneratedReport::latest('id')->first();

    expect($report)->not->toBeNull()
        ->and($report->filters)->toBeArray()
        ->and($report->filters['start_date'])->toBe('2025-01-01')
        ->and($report->filters['end_date'])->toBe('2025-12-31');
});

test('filters with null dates are saved to generated report', function () {
    Livewire::test(ReportGenerator::class)
        ->set('reportType', 'EntitiesListReport')
        ->call('generateReport');

    $report = GeneratedReport::latest('id')->first();

    expect($report)->not->toBeNull()
        ->and($report->filters)->toBeArray()
        ->and($report->filters['start_date'])->toBeNull()
        ->and($report->filters['end_date'])->toBeNull();
});

test('date range column displays dates when filters are present', function () {
    GeneratedReport::create([
        'generated_by' => $this->user->id,
        'name' => 'Test Report',
        'status' => 'ready',
        'generated_on' => now(),
        'filters' => [
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
        ],
    ]);

    Livewire::test(ReportGenerator::class)
        ->assertSee('01/01/2025')
        ->assertSee('31/12/2025');
});

test('date range column shows no date range when filters are empty', function () {
    GeneratedReport::create([
        'generated_by' => $this->user->id,
        'name' => 'Test Report',
        'status' => 'ready',
        'generated_on' => now(),
        'filters' => [],
    ]);

    Livewire::test(ReportGenerator::class)
        ->assertSee(__('reports.no_date_range'));
});
