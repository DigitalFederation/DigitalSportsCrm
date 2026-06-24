<?php

use Domain\Certifications\Actions\DeleteCertificationAction;
use Domain\Certifications\Models\Certification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=CommitteeSeeder');
});

it('soft deletes a certification without related entities', function () {
    $certification = Certification::factory()->create();
    $action = new DeleteCertificationAction;
    expect($action($certification->id))->toBeTrue();
    $this->assertSoftDeleted('certification', ['id' => $certification->id]);
});

it('throws an exception for a non-existent certification', function () {
    $action = new DeleteCertificationAction;
    expect(fn () => $action(99999))->toThrow(ModelNotFoundException::class);
});
