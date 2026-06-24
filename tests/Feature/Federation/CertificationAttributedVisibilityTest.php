<?php

use App\Enums\CommitteeCodeEnum;
use App\Enums\UserGroupEnum;
use App\Models\Committee;
use App\Models\User;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Federations\Models\Federation;
use Illuminate\Support\Facades\DB;

test('federation certification list hides all committee data when federation has no committee access', function () {
    DB::table('user_group')->insert([
        'id' => UserGroupEnum::FEDERATION->value,
        'name' => 'Federation',
        'code' => 'FEDERATION',
    ]);

    $mainFederation = Federation::factory()->create([
        'is_default_federation' => true,
        'is_local' => false,
        'parent_id' => null,
    ]);
    $federation = Federation::factory()->create([
        'parent_id' => $mainFederation->id,
        'is_default_federation' => false,
        'is_local' => false,
    ]);
    $user = User::factory()->create([
        'group_id' => UserGroupEnum::FEDERATION->value,
    ]);
    $user->federations()->attach($federation->id);

    $committee = Committee::factory()->create([
        'code' => CommitteeCodeEnum::Sport->value,
        'name' => 'Sport',
        'is_international' => false,
    ]);
    $certification = Certification::factory()->create([
        'committee_id' => $committee->id,
        'name' => 'Hidden Federation Certification',
    ]);
    CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'federation_id' => $mainFederation->id,
        'certification_name' => 'Hidden Federation Certification',
        'holder_name' => 'Hidden Certification Holder',
    ]);

    $this->actingAs($user)
        ->get(route('federation.certification-attributed.index'))
        ->assertOk()
        ->assertDontSee('Hidden Federation Certification')
        ->assertDontSee('Hidden Certification Holder');
});
