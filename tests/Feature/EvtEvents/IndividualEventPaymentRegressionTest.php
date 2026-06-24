<?php

use App\Enums\EvtEventCategoryTypeEnum;
use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use App\Models\Group;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PendingDocumentState;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows legacy full-class individual orders in the individual documents list', function () {
    $group = Group::factory()->create(['code' => 'INDIVIDUAL']);
    $individual = Individual::factory()->create();
    $user = $individual->user;
    $user->forceFill(['group_id' => $group->id])->save();

    $documentType = DocumentType::factory()->create([
        'code' => 'ORD',
        'name' => 'Order',
        'prefix' => 'ORD',
    ]);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
        'status_class' => PendingDocumentState::class,
        'owner_id' => $individual->id,
        'owner_type' => Individual::class,
        'number_extended' => 'ORD-LEGACY-INDIVIDUAL',
        'total_value' => 25,
        'net_value' => 25,
        'tax_value' => 0,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $individual->id,
        'owner_type' => Individual::class,
        'unit_value' => 25,
        'quantity' => 1,
        'net_value' => 25,
        'tax_value' => 0,
        'tax_percentage' => 0,
        'total_value' => 25,
    ]);

    $this->actingAs($user)
        ->get(route('individual.document.index'))
        ->assertOk()
        ->assertSee('ORD-LEGACY-INDIVIDUAL');
});

it('creates a payable individual order when confirming a paid open event registration', function () {
    DocumentType::factory()->create([
        'code' => 'ORD',
        'name' => 'Order',
        'prefix' => 'ORD',
    ]);

    $group = Group::factory()->create(['code' => 'INDIVIDUAL']);
    $individual = Individual::factory()->create();
    $user = $individual->user;
    $user->forceFill(['group_id' => $group->id])->save();

    $event = Event::factory()->create([
        'event_category' => EvtEventCategoryTypeEnum::organization->value,
        'status_class' => ActiveEventState::class,
        'start_registration' => now()->subDay(),
        'end_registration' => now()->addWeek(),
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonths(2),
    ]);

    $pricing = Pricing::factory()->create([
        'event_id' => $event->id,
        'discipline_id' => null,
        'price_type' => EvtEventFeeTypeEnum::PER_PERSON->value,
        'target_group' => 'individual',
        'start_date' => now()->subYears(2),
        'end_date' => now()->subYear(),
        'price' => 50,
        'is_active' => true,
        'enrollment_role' => EvtEventEnrollmentRoleEnum::INDIVIDUAL->value,
    ]);

    $this->actingAs($user)
        ->post(route('individual.evt-events.enrollments.store'), [
            'event_id' => $event->id,
            'price_id' => $pricing->id,
        ])
        ->assertRedirect(route('individual.evt-events.events.waiting-list.index', $event));

    $enrollment = Enrollment::query()
        ->where('event_id', $event->id)
        ->where('enrollable_id', $individual->id)
        ->firstOrFail();

    expect((int) $enrollment->pricing_id)->toBe($pricing->id);

    $response = $this->actingAs($user)
        ->post(route('individual.evt-events.events.waiting-list.store', $event));

    $document = Document::query()->firstOrFail();

    $response->assertRedirect(route('individual.document.show', $document->id));

    expect($document->owner_id)->toBe($individual->id)
        ->and($document->owner_type)->toBe('individual')
        ->and((float) $document->total_value)->toBe(50.0);

    $enrollment->refresh();

    expect($enrollment->document_id)->toBe($document->id)
        ->and((float) $enrollment->total_price)->toBe(50.0)
        ->and($enrollment->activated_at)->not->toBeNull();

    $this->assertDatabaseHas('document_detail', [
        'document_id' => $document->id,
        'owner_id' => $enrollment->id,
        'owner_type' => Enrollment::class,
    ]);
});
