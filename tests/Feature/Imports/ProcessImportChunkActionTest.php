<?php

use App\Models\Country;
use App\Models\User;
use Domain\Imports\Actions\BulkInsertIndividualsAction;
use Domain\Imports\Actions\DetectDuplicatesAction;
use Domain\Imports\Actions\ProcessImportChunkAction;
use Domain\Imports\Actions\ValidateBulkDataAction;
use Domain\Imports\Models\Import;
use Domain\Imports\Models\ImportError;

beforeEach(function () {
    \App\Models\Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);

    $this->user = User::factory()->create();
    $this->country = Country::factory()->create(['name' => 'Brazil']);

    // Create a default federation for the CreateIndividualAction
    \Domain\Federations\Models\Federation::factory()->create([
        'is_default_federation' => true,
        'name' => 'Default Federation',
    ]);

    $this->import = Import::create([
        'user_id' => $this->user->id,
        'type' => 'individual',
        'filename' => 'test.csv',
        'file_path' => 'imports/test.csv',
        'status' => 'processing',
        'field_mapping' => [
            'Name' => 'name',
            'Surname' => 'surname',
            'Email' => 'email',
            'Birth Date' => 'birthdate',
            'Country' => 'country',
        ],
        'options' => ['duplicate_strategy' => 'skip'],
        'total_rows' => 3,
    ]);

    $this->action = new ProcessImportChunkAction(
        new ValidateBulkDataAction,
        new DetectDuplicatesAction,
        new BulkInsertIndividualsAction
    );
});

test('processes valid chunk successfully', function () {
    $chunk = [
        [
            'Name' => 'John',
            'Surname' => 'Doe',
            'Email' => 'john@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
        ],
        [
            'Name' => 'Jane',
            'Surname' => 'Smith',
            'Email' => 'jane@example.com',
            'Birth Date' => '1992-05-15',
            'Country' => 'Brazil',
        ],
    ];

    $this->action->execute($this->import->id, $chunk, 0);

    $this->import->refresh();

    expect($this->import->processed_rows)->toBe(2)
        ->and($this->import->success_count)->toBe(2)
        ->and($this->import->error_count)->toBe(0)
        ->and(User::where('email', 'john@example.com')->exists())->toBeTrue()
        ->and(User::where('email', 'jane@example.com')->exists())->toBeTrue();
});

test('handles validation errors', function () {
    $chunk = [
        [
            'Name' => '',  // Missing name
            'Surname' => 'Invalid',
            'Email' => 'invalid@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Unknown Country',
        ],
        [
            'Name' => 'Valid',
            'Surname' => 'User',
            'Email' => 'valid@example.com',
            'Birth Date' => '1992-01-01',
            'Country' => 'Brazil',
        ],
    ];

    $this->action->execute($this->import->id, $chunk, 0);

    $this->import->refresh();

    expect($this->import->processed_rows)->toBe(2)
        ->and($this->import->success_count)->toBe(1)
        ->and($this->import->error_count)->toBe(1)
        ->and(ImportError::where('import_id', $this->import->id)->count())->toBe(1)
        ->and(User::where('email', 'valid@example.com')->exists())->toBeTrue()
        ->and(User::where('email', 'invalid@example.com')->exists())->toBeFalse();
});

test('updates duplicates with update strategy', function () {
    $this->import->update(['options' => ['duplicate_strategy' => 'update']]);

    $individual = \Domain\Individuals\Models\Individual::factory()->create([
        'email' => 'existing@example.com',
        'name' => 'Old Name',
    ]);

    $chunk = [
        [
            'Name' => 'New Name',
            'Surname' => $individual->surname,
            'Email' => 'existing@example.com',
            'Birth Date' => $individual->birthdate,
            'Country' => 'Brazil',
        ],
    ];

    $this->action->execute($this->import->id, $chunk, 0);

    $individual->refresh();
    expect($individual->name)->toBe('New Name');
});

test('marks import as completed when all rows processed', function () {
    $this->import->update(['processed_rows' => 1]);

    $chunk = [
        [
            'Name' => 'John',
            'Surname' => 'Doe',
            'Email' => 'john@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
        ],
        [
            'Name' => 'Jane',
            'Surname' => 'Smith',
            'Email' => 'jane@example.com',
            'Birth Date' => '1992-01-01',
            'Country' => 'Brazil',
        ],
    ];

    $this->action->execute($this->import->id, $chunk, 0);

    $this->import->refresh();

    expect($this->import->processed_rows)->toBe(3)
        ->and($this->import->status)->toBe('completed');
});

test('ignores cancelled imports', function () {
    $this->import->markAsCancelled();

    $chunk = [
        [
            'Name' => 'Test',
            'Surname' => 'User',
            'Email' => 'test@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
        ],
    ];

    $this->action->execute($this->import->id, $chunk, 0);

    expect(User::where('email', 'test@example.com')->exists())->toBeFalse();
});

test('maps fields correctly', function () {
    $chunk = [
        [
            'Name' => 'john',  // Should be normalized
            'Surname' => 'DOE',  // Should be normalized
            'Email' => 'JOHN@EXAMPLE.COM',  // Should be lowercase
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
        ],
    ];

    $this->action->execute($this->import->id, $chunk, 0);

    $individual = \Domain\Individuals\Models\Individual::where('email', 'john@example.com')->first();

    expect($individual)->not->toBeNull()
        ->and($individual->email)->toBe('john@example.com');
});

test('allows importing individuals for orphaned users from failed import attempts', function () {
    // SCENARIO: Previous import created users but Individual insert failed (e.g., due to FK violation)
    // Now we have "orphaned" users without individuals
    // The import should succeed for these users, not reject them as duplicates

    // Create orphaned users (users WITHOUT individuals) - simulating failed imports
    $orphanedUser1 = User::factory()->create(['email' => 'orphan1@example.com']);
    $orphanedUser2 = User::factory()->create(['email' => 'orphan2@example.com']);

    // Verify they have no individuals
    expect($orphanedUser1->individual)->toBeNull();
    expect($orphanedUser2->individual)->toBeNull();

    // Now try to import these same emails
    $chunk = [
        [
            'Name' => 'Orphan',
            'Surname' => 'One',
            'Email' => 'orphan1@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
        ],
        [
            'Name' => 'Orphan',
            'Surname' => 'Two',
            'Email' => 'orphan2@example.com',
            'Birth Date' => '1991-02-02',
            'Country' => 'Brazil',
        ],
    ];

    $this->import->update(['total_rows' => 2]);
    $this->action->execute($this->import->id, $chunk, 0);

    $this->import->refresh();

    // Should succeed - not reject as duplicates
    expect($this->import->success_count)->toBe(2, 'Both orphaned users should be successfully imported')
        ->and($this->import->error_count)->toBe(0, 'No errors should occur');

    // Verify individuals were created and linked to existing users
    $individual1 = \Domain\Individuals\Models\Individual::where('email', 'orphan1@example.com')->first();
    $individual2 = \Domain\Individuals\Models\Individual::where('email', 'orphan2@example.com')->first();

    expect($individual1)->not->toBeNull()
        ->and($individual1->user_id)->toBe($orphanedUser1->id)
        ->and($individual2)->not->toBeNull()
        ->and($individual2->user_id)->toBe($orphanedUser2->id);
});

test('rejects import when user AND individual already exist', function () {
    // Create a user WITH an individual (proper duplicate)
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);
    \Domain\Individuals\Models\Individual::factory()->create([
        'email' => 'existing@example.com',
        'user_id' => $existingUser->id,
    ]);

    $chunk = [
        [
            'Name' => 'Duplicate',
            'Surname' => 'User',
            'Email' => 'existing@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
        ],
    ];

    $this->import->update(['total_rows' => 1]);
    $this->action->execute($this->import->id, $chunk, 0);

    $this->import->refresh();

    // Should fail - this is a real duplicate
    expect($this->import->error_count)->toBe(1, 'Should reject real duplicates');
});

test('handles district_id with invalid values through full import flow', function () {
    // Add district_id to field mapping
    $this->import->update([
        'field_mapping' => [
            'Name' => 'name',
            'Surname' => 'surname',
            'Email' => 'email',
            'Birth Date' => 'birthdate',
            'Country' => 'country',
            'District' => 'district_id',
        ],
        'total_rows' => 3,
    ]);

    $chunk = [
        [
            'Name' => 'Test',
            'Surname' => 'Zero',
            'Email' => 'test.zero@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
            'District' => '0',  // Invalid - would cause FK violation
        ],
        [
            'Name' => 'Test',
            'Surname' => 'Invalid',
            'Email' => 'test.invalid@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
            'District' => 'NonExistentDistrict',  // Invalid name
        ],
        [
            'Name' => 'Test',
            'Surname' => 'Empty',
            'Email' => 'test.empty@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
            'District' => '',  // Empty - should be null
        ],
    ];

    $this->action->execute($this->import->id, $chunk, 0);

    $this->import->refresh();

    // All should succeed with district_id as null (not cause FK violation)
    expect($this->import->success_count)->toBe(3, 'All records should import successfully')
        ->and($this->import->error_count)->toBe(0, 'No FK violations should occur');

    // Verify all individuals have null district_id
    $individuals = \Domain\Individuals\Models\Individual::whereIn('email', [
        'test.zero@example.com',
        'test.invalid@example.com',
        'test.empty@example.com',
    ])->get();

    expect($individuals)->toHaveCount(3);
    foreach ($individuals as $individual) {
        expect($individual->district_id)->toBeNull("Individual {$individual->email} should have null district_id");
    }
});

test('handles zone_ids with invalid values through full import flow', function () {
    // Add zone_ids to field mapping
    $this->import->update([
        'field_mapping' => [
            'Name' => 'name',
            'Surname' => 'surname',
            'Email' => 'email',
            'Birth Date' => 'birthdate',
            'Country' => 'country',
            'Zone' => 'zone_ids',
        ],
        'total_rows' => 3,
    ]);

    $chunk = [
        [
            'Name' => 'Test',
            'Surname' => 'Zero',
            'Email' => 'test.zone.zero@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
            'Zone' => '0',  // Invalid - would cause FK violation
        ],
        [
            'Name' => 'Test',
            'Surname' => 'Invalid',
            'Email' => 'test.zone.invalid@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
            'Zone' => '999999',  // Non-existent zone ID
        ],
        [
            'Name' => 'Test',
            'Surname' => 'Empty',
            'Email' => 'test.zone.empty@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
            'Zone' => '',  // Empty - should be null
        ],
    ];

    $this->action->execute($this->import->id, $chunk, 0);

    $this->import->refresh();

    // All should succeed with zone_ids as null (not cause FK violation)
    expect($this->import->success_count)->toBe(3, 'All records should import successfully')
        ->and($this->import->error_count)->toBe(0, 'No FK violations should occur');

    // Verify all individuals have no zones attached
    $individuals = \Domain\Individuals\Models\Individual::whereIn('email', [
        'test.zone.zero@example.com',
        'test.zone.invalid@example.com',
        'test.zone.empty@example.com',
    ])->get();

    expect($individuals)->toHaveCount(3);
    foreach ($individuals as $individual) {
        expect($individual->zones()->count())->toBe(0, "Individual {$individual->email} should have no zones");
    }
});

test('normalizes gender values in Portuguese', function () {
    $this->import->update([
        'field_mapping' => [
            'Name' => 'name',
            'Surname' => 'surname',
            'Email' => 'email',
            'Birth Date' => 'birthdate',
            'Country' => 'country',
            'Gender' => 'gender',
        ],
        'total_rows' => 3,
    ]);

    $chunk = [
        [
            'Name' => 'Test',
            'Surname' => 'Male',
            'Email' => 'test.male@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
            'Gender' => 'Masculino',
        ],
        [
            'Name' => 'Test',
            'Surname' => 'Female',
            'Email' => 'test.female@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
            'Gender' => 'Feminino',
        ],
    ];

    $this->action->execute($this->import->id, $chunk, 0);

    $this->import->refresh();

    expect($this->import->success_count)->toBe(2, 'All records should import successfully')
        ->and($this->import->error_count)->toBe(0);

    $indMale = \Domain\Individuals\Models\Individual::where('email', 'test.male@example.com')->first();
    $indFemale = \Domain\Individuals\Models\Individual::where('email', 'test.female@example.com')->first();

    expect($indMale->gender)->toBe('male', 'Masculino should be normalized to male')
        ->and($indFemale->gender)->toBe('female', 'Feminino should be normalized to female');
});

test('imports member_number from CSV when provided', function () {
    // SCENARIO: PM imports individuals with their existing member numbers
    // The imported member_number should be preserved

    $this->import->update([
        'field_mapping' => [
            'Name' => 'name',
            'Surname' => 'surname',
            'Email' => 'email',
            'Birth Date' => 'birthdate',
            'Country' => 'country',
            'Member Number' => 'member_number',
        ],
        'total_rows' => 2,
    ]);

    $chunk = [
        [
            'Name' => 'User',
            'Surname' => 'One',
            'Email' => 'member.import.one@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
            'Member Number' => '50001',  // Existing member number from PM
        ],
        [
            'Name' => 'User',
            'Surname' => 'Two',
            'Email' => 'member.import.two@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
            'Member Number' => '50002',  // Existing member number from PM
        ],
    ];

    $this->action->execute($this->import->id, $chunk, 0);

    $this->import->refresh();

    expect($this->import->success_count)->toBe(2, 'All records should import successfully')
        ->and($this->import->error_count)->toBe(0, 'No errors should occur');

    // Verify member_number values are preserved
    $ind1 = \Domain\Individuals\Models\Individual::where('email', 'member.import.one@example.com')->first();
    $ind2 = \Domain\Individuals\Models\Individual::where('email', 'member.import.two@example.com')->first();

    expect($ind1->member_number)->toBe(50001, 'Imported member number should be preserved')
        ->and($ind2->member_number)->toBe(50002, 'Imported member number should be preserved');
});

test('auto-assigns member_number when not provided in CSV', function () {
    // SCENARIO: Import without member_number - system should auto-assign

    $this->import->update([
        'field_mapping' => [
            'Name' => 'name',
            'Surname' => 'surname',
            'Email' => 'email',
            'Birth Date' => 'birthdate',
            'Country' => 'country',
        ],
        'total_rows' => 2,
    ]);

    $chunk = [
        [
            'Name' => 'Auto',
            'Surname' => 'One',
            'Email' => 'auto.member.one@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
        ],
        [
            'Name' => 'Auto',
            'Surname' => 'Two',
            'Email' => 'auto.member.two@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
        ],
    ];

    $this->action->execute($this->import->id, $chunk, 0);

    $this->import->refresh();

    expect($this->import->success_count)->toBe(2, 'All records should import successfully')
        ->and($this->import->error_count)->toBe(0, 'No errors should occur');

    // Verify member_number was auto-assigned
    $ind1 = \Domain\Individuals\Models\Individual::where('email', 'auto.member.one@example.com')->first();
    $ind2 = \Domain\Individuals\Models\Individual::where('email', 'auto.member.two@example.com')->first();

    expect($ind1->member_number)->not->toBeNull('Member number should be auto-assigned')
        ->and($ind2->member_number)->not->toBeNull('Member number should be auto-assigned')
        ->and($ind1->member_number)->not->toBe($ind2->member_number, 'Each individual should have unique member number');
});

test('handles member_number with empty/zero values - converts to null and auto-assigns', function () {
    // SCENARIO: CSV has member_number column with empty or '0' values
    // These should be treated as "no number" and auto-assigned

    $this->import->update([
        'field_mapping' => [
            'Name' => 'name',
            'Surname' => 'surname',
            'Email' => 'email',
            'Birth Date' => 'birthdate',
            'Country' => 'country',
            'Member Number' => 'member_number',
        ],
        'total_rows' => 3,
    ]);

    $chunk = [
        [
            'Name' => 'User',
            'Surname' => 'Empty',
            'Email' => 'member.empty@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
            'Member Number' => '',  // Empty - should auto-assign
        ],
        [
            'Name' => 'User',
            'Surname' => 'Zero',
            'Email' => 'member.zero@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
            'Member Number' => '0',  // Zero - should auto-assign
        ],
        [
            'Name' => 'User',
            'Surname' => 'Valid',
            'Email' => 'member.valid@example.com',
            'Birth Date' => '1990-01-01',
            'Country' => 'Brazil',
            'Member Number' => '99999',  // Valid - should preserve
        ],
    ];

    $this->action->execute($this->import->id, $chunk, 0);

    $this->import->refresh();

    expect($this->import->success_count)->toBe(3, 'All records should import successfully')
        ->and($this->import->error_count)->toBe(0, 'No errors should occur');

    $indEmpty = \Domain\Individuals\Models\Individual::where('email', 'member.empty@example.com')->first();
    $indZero = \Domain\Individuals\Models\Individual::where('email', 'member.zero@example.com')->first();
    $indValid = \Domain\Individuals\Models\Individual::where('email', 'member.valid@example.com')->first();

    // Empty and zero should get auto-assigned (not null, not 0)
    expect($indEmpty->member_number)->not->toBeNull('Empty should be auto-assigned')
        ->and($indEmpty->member_number)->toBeGreaterThan(0, 'Should be a positive number')
        ->and($indZero->member_number)->not->toBeNull('Zero should be auto-assigned')
        ->and($indZero->member_number)->toBeGreaterThan(0, 'Should be a positive number')
        ->and($indValid->member_number)->toBe(99999, 'Valid member number should be preserved');
});
