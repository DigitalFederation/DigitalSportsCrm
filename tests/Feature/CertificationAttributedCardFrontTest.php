<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    // Pin each user group to the id declared by UserGroupEnum so isIndividual()
    // and friends (which compare group_id against the enum value) work
    // regardless of autoincrement drift across tests.
    foreach (UserGroupEnum::cases() as $case) {
        Group::firstOrCreate(
            ['id' => $case->value],
            ['code' => $case->name, 'name' => ucfirst(strtolower($case->name))]
        );
    }

    Storage::fake('public');
});

function renderCardFront(CertificationAttributed $attributed): string
{
    return view('components.certification_attributed.card_front', [
        'certificationAttributed' => $attributed,
    ])->render();
}

function makeAttributedWithImage(?string $image = 'example.jpg'): CertificationAttributed
{
    if ($image !== null) {
        Storage::disk('public')->put('img/cards/'.$image, 'fake-card-image');
    }

    $certification = Certification::factory()->create([
        'certification_view' => $image,
    ]);

    return CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
    ]);
}

it('renders the certification image for ADMIN users', function (): void {
    $admin = User::factory()->create(['group_id' => UserGroupEnum::ADMIN->value]);

    actingAs($admin);

    $html = renderCardFront(makeAttributedWithImage('admin-test.jpg'));

    expect($html)->toContain('img/cards/admin-test.jpg');
});

it('renders the certification image for FEDERATION users', function (): void {
    $user = User::factory()->create(['group_id' => UserGroupEnum::FEDERATION->value]);

    actingAs($user);

    $html = renderCardFront(makeAttributedWithImage('federation-test.jpg'));

    expect($html)->toContain('img/cards/federation-test.jpg');
});

it('renders the certification image for ENTITY users', function (): void {
    $user = User::factory()->create(['group_id' => UserGroupEnum::ENTITY->value]);

    actingAs($user);

    $html = renderCardFront(makeAttributedWithImage('entity-test.jpg'));

    expect($html)->toContain('img/cards/entity-test.jpg');
});

it('wraps the image in the individual card link for INDIVIDUAL users', function (): void {
    $user = User::factory()->create(['group_id' => UserGroupEnum::INDIVIDUAL->value]);
    Individual::factory()->for($user, 'user')->create();

    actingAs($user);

    $html = renderCardFront(makeAttributedWithImage('individual-test.jpg'));

    expect($html)
        ->toContain('img/cards/individual-test.jpg')
        ->toContain('certification-card');
});

it('falls back to the default card image when certification_view is empty', function (): void {
    $admin = User::factory()->create(['group_id' => UserGroupEnum::ADMIN->value]);

    actingAs($admin);

    $html = renderCardFront(makeAttributedWithImage(null));

    expect($html)->toContain('default_certification_card.jpg');
});
