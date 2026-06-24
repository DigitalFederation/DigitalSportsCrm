<?php

use App\Models\Group;
use Database\Factories\UserFactory;
use Domain\Documents\Models\Document;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');

    $this->group = Group::factory()->create([
        'code' => 'ADMIN',
    ]);

    $this->user = UserFactory::new()->create([
        'group_id' => $this->group->id,
    ]);

    $this->user->givePermissionTo('access documents');

});

it('successfully deletes a canceled document without associated transactions', function () {

    // Login in as a user with the right permissions
    $this->actingAs($this->user);

    // Arrange
    $document = Document::factory()->state([
        'status_class' => \Domain\Documents\States\CanceledDocumentState::class,
    ])->create();

    // Act
    $response = $this->delete(route('admin.document.delete-canceled', $document->id));

    // Assert
    $response->assertRedirect(route('admin.document.index'));
    $response->assertSessionHas('success');
    $this->assertSoftDeleted('document', ['id' => $document->id]);
});

it('fails to delete a non-canceled document', function () {

    // Login in as a user with the right permissions
    $this->actingAs($this->user);

    // Arrange
    $document = Document::factory()->create([
        'status_class' => \Domain\Documents\States\DraftDocumentState::class,
    ]);

    // Act
    $response = $this->delete(route('admin.document.delete-canceled', $document->id));

    // Assert
    $response->assertRedirect();
    $response->assertSessionHasErrors('message');
});

it('fails to delete a canceled document with associated transactions', function () {

    // Login in as a user with the right permissions
    $this->actingAs($this->user);

    // Arrange
    $document = Document::factory()->state([
        'status_class' => \Domain\Documents\States\CanceledDocumentState::class,
    ])->create();

    PaymentTransaction::factory()->create(['document_id' => $document->id]);

    // Act
    $response = $this->delete(route('admin.document.delete-canceled', $document->id));

    // Assert
    $response->assertRedirect();
    $response->assertSessionHasErrors('message');
});
