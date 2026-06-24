<?php

use App\Enums\UserGroupEnum;
use App\Livewire\Reports\IndividualInsuranceReportManager;
use App\Models\Group;
use App\Models\User;
use Domain\Reports\Models\GeneratedReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    Group::query()->delete();
    Group::insert([
        ['id' => 1, 'name' => 'Individual', 'code' => 'INDIVIDUAL'],
        ['id' => 2, 'name' => 'Entity', 'code' => 'ENTITY'],
        ['id' => 3, 'name' => 'Federation', 'code' => 'FEDERATION'],
        ['id' => 5, 'name' => 'Admin', 'code' => 'ADMIN'],
    ]);

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'access memberships', 'guard_name' => 'web']);

    $this->adminUser = User::factory()->create([
        'group_id' => UserGroupEnum::ADMIN->value,
    ]);
    $this->adminUser->assignRole('admin');
    $this->adminUser->givePermissionTo('access memberships');
});

it('renders the insurance reports page', function () {
    actingAs($this->adminUser)
        ->get(route('admin.insurance-reports.index'))
        ->assertSuccessful()
        ->assertSee(__('reports.insurance_reports_title'))
        ->assertSeeLivewire(IndividualInsuranceReportManager::class);
});

it('requires authentication to access the page', function () {
    $this->get(route('admin.insurance-reports.index'))
        ->assertRedirect();
});

it('lists only insurance reports in the table', function () {
    GeneratedReport::create([
        'name' => __('reports.individual_insurances_list'),
        'generated_by' => $this->adminUser->id,
        'status' => 'ready',
        'generated_on' => now(),
        'file_path' => '/reports/test.xlsx',
        'filters' => [],
    ]);

    GeneratedReport::create([
        'name' => __('reports.entity_insurances_list'),
        'generated_by' => $this->adminUser->id,
        'status' => 'ready',
        'generated_on' => now(),
        'file_path' => '/reports/entity.xlsx',
        'filters' => [],
    ]);

    GeneratedReport::create([
        'name' => 'Some Other Report',
        'generated_by' => $this->adminUser->id,
        'status' => 'ready',
        'generated_on' => now(),
        'file_path' => '/reports/other.xlsx',
        'filters' => [],
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(IndividualInsuranceReportManager::class)
        ->assertSee(__('main.individual_type'))
        ->assertSee(__('main.entity_type'))
        ->assertDontSee('Some Other Report');
});

it('dispatches individual insurance report generation', function () {
    Livewire::actingAs($this->adminUser)
        ->test(IndividualInsuranceReportManager::class)
        ->set('reportType', 'individual')
        ->set('startDate', '2025-01-01')
        ->set('endDate', '2025-12-31')
        ->call('generateReport')
        ->assertSet('generatingReport', true);

    $this->assertDatabaseHas('generated_reports', [
        'name' => __('reports.individual_insurances_list'),
        'generated_by' => $this->adminUser->id,
    ]);
});

it('dispatches entity insurance report generation', function () {
    Livewire::actingAs($this->adminUser)
        ->test(IndividualInsuranceReportManager::class)
        ->set('reportType', 'entity')
        ->set('startDate', '2025-01-01')
        ->set('endDate', '2025-12-31')
        ->call('generateReport')
        ->assertSet('generatingReport', true);

    $this->assertDatabaseHas('generated_reports', [
        'name' => __('reports.entity_insurances_list'),
        'generated_by' => $this->adminUser->id,
    ]);
});

it('deletes a report via livewire', function () {
    $report = GeneratedReport::create([
        'name' => __('reports.individual_insurances_list'),
        'generated_by' => $this->adminUser->id,
        'status' => 'ready',
        'generated_on' => now(),
        'filters' => [],
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(IndividualInsuranceReportManager::class)
        ->call('deleteReport', $report->id);

    $this->assertDatabaseMissing('generated_reports', ['id' => $report->id]);
});

it('updates insurer status via livewire', function () {
    $report = GeneratedReport::create([
        'name' => __('reports.individual_insurances_list'),
        'generated_by' => $this->adminUser->id,
        'status' => 'ready',
        'insurer_status' => 'pending',
        'generated_on' => now(),
        'filters' => [],
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(IndividualInsuranceReportManager::class)
        ->call('updateInsurerStatus', $report->id, 'sent');

    $this->assertDatabaseHas('generated_reports', [
        'id' => $report->id,
        'insurer_status' => 'sent',
    ]);
});

it('transitions insurer status from sent to completed', function () {
    $report = GeneratedReport::create([
        'name' => __('reports.entity_insurances_list'),
        'generated_by' => $this->adminUser->id,
        'status' => 'ready',
        'insurer_status' => 'sent',
        'generated_on' => now(),
        'filters' => [],
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(IndividualInsuranceReportManager::class)
        ->call('updateInsurerStatus', $report->id, 'completed');

    $this->assertDatabaseHas('generated_reports', [
        'id' => $report->id,
        'insurer_status' => 'completed',
    ]);
});

it('generates both report types via artisan command', function () {
    artisan('reports:generate-weekly-insurance')
        ->assertSuccessful();

    $this->assertDatabaseHas('generated_reports', [
        'name' => __('reports.individual_insurances_list'),
        'generated_by' => null,
    ]);

    $this->assertDatabaseHas('generated_reports', [
        'name' => __('reports.entity_insurances_list'),
        'generated_by' => null,
    ]);
});
