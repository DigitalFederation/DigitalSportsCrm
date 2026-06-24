<?php

use App\Models\Group;
use App\Models\User;
use Domain\Certifications\Actions\ActivateCertificationAttributedAction;
use Domain\Certifications\Actions\ActivateCertificationAttributedByFederationAction;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\States\ActiveMembershipState;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=UserGroupSeeder');
    artisan('db:seed --class=RoleAndPermissionSeeder');
    artisan('db:seed --class=DocumentTypeSeeder');

    $this->activateCertificationAction = new ActivateCertificationAttributedAction;
    $this->activateCertificationByFederationAction = new ActivateCertificationAttributedByFederationAction(
        $this->activateCertificationAction
    );

    // Create default federation with active membership
    $federation = Federation::factory()->create(['is_default_federation' => true]);
    Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => ActiveMembershipState::class,
    ]);
});

/*
 * Need an individual with a federation to assign a certification
 */
it('can store a certification attributed using the store method', function (string $role) {
    $entity = Entity::factory()->create();
    $status_class = ActiveCertificationAttributedState::class;
    $group = Group::factory()->create(['code' => 'INDIVIDUAL']);
    $federation = Federation::factory()->create();

    // Create active membership for federation
    Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => ActiveMembershipState::class,
    ]);

    $certification = Certification::factory()->create();
    $userForIndividual = User::factory()->for($group, 'group')->create(); // Ensure the user is created with a group
    $individual = Individual::factory()->for($userForIndividual, 'user')->create(); // Create an individual for the user
    $individual->federations()->attach($federation->id);

    // Create instructor with proper user relationship
    $instructorUser = User::factory()->create();
    $instructor = Individual::factory()->for($instructorUser, 'user')->create();
    $instructor->federations()->attach($federation->id);

    $national_code = '123456';
    $certification_name = 'Test Certification';
    $holder_name = 'Test Holder';
    $federation_name = 'Test Federation';
    $entity_name = 'Test Entity';

    $code = '123';
    $number = '456';

    $authUser = User::factory()->create(['group_id' => Group::where('code', strtoupper($role))->first()->id]);
    $role == 'ADMIN' ? $authUser->assignRole('admin') : $authUser->assignRole(['federation-admin', 'association-sport-admin']);

    if ($role == 'FEDERATION') {
        $authUser->federations()->attach($federation->id);
    }

    $this->actingAs($authUser);

    $response = $this->post(route(strtolower($role) . '.certification-attributed.store'), [
        'certification_id' => $certification->id,
        'federation_id' => $federation->id,
        'entity_id' => $entity->id,
        'status_class' => $status_class,
        'individual' => [
            'name' => [$individual->name],
            'national_code' => [$national_code],
            'id' => [$individual->id],
        ],
        'national_code' => $national_code,
        'certification_name' => $certification_name,
        'holder_name' => $holder_name,
        'federation_name' => $federation_name,
        'entity_name' => $entity_name,
        'instructor_id' => $instructor->id,
        'code' => $code,
        'number' => $number,
        'activator_id' => $federation->id,
        'activator_type' => get_class($federation),
        'current_term_starts_at' => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect();

    $response->assertSessionHas('success', 'Certification attributed with success.');
})->with(['FEDERATION']);
