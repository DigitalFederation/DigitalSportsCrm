<?php

use App\Enums\CommitteeCodeEnum;
use App\Enums\UserGroupEnum;
use App\Models\Committee;
use App\Models\User;
use Domain\Documents\Actions\DocumentDownloadAuthorizationAction;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;

function makeFederationUserForDocumentAuthorization(Federation $federation): User
{
    $user = User::factory()->create([
        'group_id' => UserGroupEnum::FEDERATION->value,
    ]);

    $user->federations()->attach($federation->id);

    return $user;
}

function makeDivingLicenseDocumentForFederation(Federation $federation, Committee $committee): Document
{
    $license = License::factory()->create([
        'committee_id' => $committee->id,
    ]);

    $licenseAttributed = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $federation->id,
    ]);

    $document = Document::factory()->create();

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_type' => LicenseAttributed::class,
        'owner_id' => $licenseAttributed->id,
    ]);

    return $document;
}

test('federation can view diving license document details for its own federation', function () {
    $mainFederation = Federation::factory()->create([
        'is_default_federation' => true,
        'is_local' => false,
        'parent_id' => null,
    ]);
    $viewerFederation = Federation::factory()->create([
        'parent_id' => $mainFederation->id,
        'is_local' => false,
        'is_default_federation' => false,
    ]);
    $divingCommittee = Committee::factory()->international()->create([
        'code' => CommitteeCodeEnum::Diving->value,
        'name' => 'Diving',
    ]);
    $viewerFederation->committees()->attach($divingCommittee->id);

    $document = makeDivingLicenseDocumentForFederation($viewerFederation, $divingCommittee);
    $user = makeFederationUserForDocumentAuthorization($viewerFederation);

    expect((new DocumentDownloadAuthorizationAction)->execute($user, $document))->toBeTrue();
});

test('federation cannot view sibling federation diving license documents', function () {
    $mainFederation = Federation::factory()->create([
        'is_default_federation' => true,
        'is_local' => false,
        'parent_id' => null,
    ]);
    $viewerFederation = Federation::factory()->create([
        'parent_id' => $mainFederation->id,
        'is_local' => false,
        'is_default_federation' => false,
    ]);
    $siblingFederation = Federation::factory()->create([
        'parent_id' => $mainFederation->id,
        'is_local' => false,
        'is_default_federation' => false,
    ]);
    $divingCommittee = Committee::factory()->international()->create([
        'code' => CommitteeCodeEnum::Diving->value,
        'name' => 'Diving',
    ]);
    $viewerFederation->committees()->attach($divingCommittee->id);

    $document = makeDivingLicenseDocumentForFederation($siblingFederation, $divingCommittee);
    $user = makeFederationUserForDocumentAuthorization($viewerFederation);

    expect((new DocumentDownloadAuthorizationAction)->execute($user, $document))->toBeFalse();
});

test('federation cannot view committee-scoped documents without committee access', function () {
    $mainFederation = Federation::factory()->create([
        'is_default_federation' => true,
        'is_local' => false,
        'parent_id' => null,
    ]);
    $viewerFederation = Federation::factory()->create([
        'parent_id' => $mainFederation->id,
        'is_local' => false,
        'is_default_federation' => false,
    ]);
    $divingCommittee = Committee::factory()->international()->create([
        'code' => CommitteeCodeEnum::Diving->value,
        'name' => 'Diving',
    ]);

    $document = makeDivingLicenseDocumentForFederation($viewerFederation, $divingCommittee);
    $user = makeFederationUserForDocumentAuthorization($viewerFederation);

    expect((new DocumentDownloadAuthorizationAction)->execute($user, $document))->toBeFalse();
});
