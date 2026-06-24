<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\Models\InsurancePlan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    Group::query()->delete();
    Group::insert([
        ['id' => 1, 'name' => 'Individual', 'code' => 'INDIVIDUAL'],
        ['id' => 2, 'name' => 'Entity', 'code' => 'ENTITY'],
        ['id' => 3, 'name' => 'Federation', 'code' => 'FEDERATION'],
        ['id' => 5, 'name' => 'Admin', 'code' => 'ADMIN'],
    ]);

    $this->admin = User::factory()->create([
        'group_id' => UserGroupEnum::ADMIN->value,
    ]);
    $this->admin->assignRole('admin');

    $this->actingAs($this->admin);
});

test('insurance plan can store insurer contact information', function () {
    $insurancePlan = InsurancePlan::factory()->create([
        'insurer_address' => '123 Insurance Street, Lisbon',
        'insurer_email' => 'contact@example.test',
        'insurer_phone' => '+351 123 456 789',
    ]);

    expect($insurancePlan->insurer_address)->toBe('123 Insurance Street, Lisbon')
        ->and($insurancePlan->insurer_email)->toBe('contact@example.test')
        ->and($insurancePlan->insurer_phone)->toBe('+351 123 456 789');
});

test('insurance plan can store coverage details', function () {
    $insurancePlan = InsurancePlan::factory()->create([
        'applicable_deductibles' => 'Deductible: €500 per claim for property damage',
        'coverage_details' => 'Personal liability: €1,000,000\nMedical expenses: €50,000',
    ]);

    expect($insurancePlan->applicable_deductibles)->toBe('Deductible: €500 per claim for property damage')
        ->and($insurancePlan->coverage_details)->toBe('Personal liability: €1,000,000\nMedical expenses: €50,000');
});

test('insured activity and territorial scope can store long text', function () {
    $longActivity = str_repeat('Diving activities including recreational, technical and professional diving. ', 20);
    $longScope = str_repeat('Coverage extends to all European countries including territorial waters. ', 20);

    $insurancePlan = InsurancePlan::factory()->create([
        'insured_activity' => $longActivity,
        'territorial_scope' => $longScope,
    ]);

    expect($insurancePlan->insured_activity)->toBe($longActivity)
        ->and($insurancePlan->territorial_scope)->toBe($longScope);
});

test('new fields are fillable in InsurancePlan model', function () {
    $fillableFields = (new InsurancePlan)->getFillable();

    expect($fillableFields)
        ->toContain('insurer_address')
        ->toContain('insurer_email')
        ->toContain('insurer_phone')
        ->toContain('applicable_deductibles')
        ->toContain('coverage_details');
});

test('insurance document PDF can be generated with new fields', function () {
    $insurancePlan = InsurancePlan::factory()->create([
        'name' => 'Test Insurance Plan',
        'insurer_address' => 'Example Street 1',
        'insurer_email' => 'insurer@example.test',
        'insurer_phone' => '+15550101000',
        'applicable_deductibles' => 'Test deductibles',
        'coverage_details' => 'Test coverage details',
        'insured_activity' => 'Test activities',
        'territorial_scope' => 'Test territorial scope',
    ]);

    $individual = Individual::factory()->create();

    $insurance = Insurance::factory()->create([
        'insurance_plan_id' => $insurancePlan->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'start_date' => now(),
        'end_date' => now()->addYear(),
        'policy_number' => 'TEST-123456',
    ]);

    $response = $this->get(route('admin.insurances.document.download', $insurance));

    $response->assertStatus(200)
        ->assertHeader('content-type', 'application/pdf');
});

test('admin can update insurance plan with new fields', function () {
    $insurancePlan = InsurancePlan::factory()->create();

    $updateData = [
        'name' => $insurancePlan->name,
        'target_audience' => $insurancePlan->target_audience,
        'type' => $insurancePlan->type->value,
        'period' => $insurancePlan->period,
        'period_unit' => $insurancePlan->period_unit,
        'individual_fee' => $insurancePlan->individual_fee,
        'entity_fee' => $insurancePlan->entity_fee,
        'vat_rate' => $insurancePlan->vat_rate,
        'insurer_address' => 'Updated Address',
        'insurer_email' => 'updated-insurer@example.test',
        'insurer_phone' => '+15550101001',
        'applicable_deductibles' => 'Updated deductibles',
        'coverage_details' => 'Updated coverage',
    ];

    $response = $this->put(route('admin.insurance-plans.update', $insurancePlan), $updateData);

    $response->assertRedirect();

    $insurancePlan->refresh();

    expect($insurancePlan->insurer_address)->toBe('Updated Address')
        ->and($insurancePlan->insurer_email)->toBe('updated-insurer@example.test')
        ->and($insurancePlan->insurer_phone)->toBe('+15550101001')
        ->and($insurancePlan->applicable_deductibles)->toBe('Updated deductibles')
        ->and($insurancePlan->coverage_details)->toBe('Updated coverage');
});
