<?php

use Domain\OfficialDocuments\Models\OfficialDocument;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(fn () => app()->setLocale('en'));

test('a slug role resolves via official_documents.roles', function () {
    $document = new OfficialDocument(['role' => 'coach']);

    expect($document->roleLabel())->toBe('Coach');
});

test('a hyphenated slug role is normalized to its translation key', function () {
    $document = new OfficialDocument(['role' => 'instructor-leader']);

    expect($document->roleLabel())->toBe('Instructor & Leader');
});

test('an uppercase professional role code resolves via professional_roles.role_types', function () {
    $document = new OfficialDocument(['role' => 'TECHNICAL_OFFICIAL']);

    expect($document->roleLabel())->toBe('Technical Official');
});

test('the individual role resolves to its label', function () {
    $document = new OfficialDocument(['role' => 'individual']);

    expect($document->roleLabel())->toBe('Individual');
});

test('a null role returns an empty string', function () {
    $document = new OfficialDocument(['role' => null]);

    expect($document->roleLabel())->toBe('');
});

test('an unknown role falls back to the raw value', function () {
    $document = new OfficialDocument(['role' => 'SOMETHING_UNMAPPED']);

    expect($document->roleLabel())->toBe('SOMETHING_UNMAPPED');
});
