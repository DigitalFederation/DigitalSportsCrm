<?php

use App\Models\Group;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
    artisan('db:seed --class=UserGroupSeeder');

    $this->federation = Federation::factory()->create();
    $this->user = \App\Models\User::factory()->create(['group_id' => Group::where('code', 'FEDERATION')->first()->id]);
    $this->user->assignRole('federation-admin');
    $this->federation->users()->attach($this->user->id);
    $this->actingAs($this->user);
});

it('can find individuals from federation user', function () {
    $individuals = Individual::factory(10)->create();
    $this->federation->individuals()->attach($individuals->pluck('id'));
    expect(Individual::all())->toHaveCount(10);
});

it('can find only individuals from federation user', function () {
    $individuals = Individual::factory(10)->create();
    $this->federation->individuals()->attach($individuals->pluck('id'));

    Individual::factory(10)->create();

    expect(Individual::all())->toHaveCount(10);
});
