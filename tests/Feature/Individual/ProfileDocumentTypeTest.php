<?php

use App\Http\Requests\UpdateIndividualProfileRequest;
use App\Models\Country;
use Domain\Geographic\Models\District;
use Illuminate\Support\Facades\Validator;

function validIndividualProfileData(array $overrides = []): array
{
    $country = Country::factory()->create();
    $district = District::factory()->create([
        'country_id' => $country->id,
    ]);

    return array_merge([
        'name' => 'Ana',
        'surname' => 'Silva',
        'native_name' => 'Ana Silva',
        'country_id' => $country->id,
        'birthdate' => '1990-01-01',
        'gender' => 'female',
        'district_id' => $district->id,
        'vat_number' => '123456789',
        'doc_ref_type' => 'passport',
        'doc_ref' => 'AA123456',
        'doc_ref_validation_date' => '2030-01-01',
    ], $overrides);
}

test('individual profile accepts every configured document type', function (string $documentType) {
    $validator = Validator::make(
        validIndividualProfileData(['doc_ref_type' => $documentType]),
        (new UpdateIndividualProfileRequest)->rules()
    );

    expect($validator->passes())->toBeTrue();
})->with([
    'identity_card',
    'citizen_card',
    'foreign_identity_card',
    'permanent_residence_card',
    'passport',
    'national_id_number',
    'passport_number',
]);

test('individual profile rejects unknown document types', function () {
    $validator = Validator::make(
        validIndividualProfileData(['doc_ref_type' => 'driver_license']),
        (new UpdateIndividualProfileRequest)->rules()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('doc_ref_type'))->toBeTrue();
});
