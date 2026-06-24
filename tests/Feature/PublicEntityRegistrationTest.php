<?php

use App\Models\Committee;
use App\Models\Country;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    // Get or create a country for testing
    $this->country = Country::first();
    if (! $this->country) {
        $this->country = Country::factory()->create([
            'name' => 'Test Country',
        ]);
    }

    // Create a district for testing
    $this->district = District::factory()->create([
        'name' => 'Test District',
        'is_active' => true,
        'country_id' => $this->country->id,
    ]);

    // Create committees for testing
    $this->sportCommittee = Committee::firstOrCreate([
        'code' => 'SPORT',
    ], [
        'name' => 'Sport Committee',
        'description' => 'Sport activities committee',
    ]);

    $this->divingCommittee = Committee::firstOrCreate([
        'code' => 'DIVING',
    ], [
        'name' => 'Diving Committee',
        'description' => 'Diving activities committee',
    ]);

    // Create federation for testing.
    $this->federation = Federation::factory()->create([
        'name' => 'International Federation Test',
        'is_local' => false,
    ]);
});

test('entity registration form loads successfully', function () {
    $response = $this->get(route('entity.registration.form'));

    $response->assertStatus(200)
        ->assertSee('Registo de Entidade')
        ->assertSee('Informação da Entidade')
        ->assertSee('Localização da Sede')
        ->assertSee('Contactos Públicos');
});

test('entity registration form displays required field indicators', function () {
    $response = $this->get(route('entity.registration.form'));

    // Check for required field indicators (asterisks)
    $response->assertSee('Nome da Entidade') // Entity name
        ->assertSee('Nome de Registo Fiscal') // Legal name
        ->assertSee('Responsável Legal') // Legal responsible person
        ->assertSee('NIF') // VAT number
        ->assertSee('Morada') // Address
        ->assertSee('Código Postal') // Postal code
        ->assertSee('Email de Contacto') // Email
        ->assertSee('Telefone'); // Phone
});

test('entity registration validates required fields', function () {
    $response = $this->post(route('entity.registration.submit'), []);

    $response->assertSessionHasErrors([
        'name',
        'legal_name',
        'legal_responsible_person',
        'vat_number',
        'address',
        'location',
        'postal_code',
        'email',
        'phone',
        'user_email',
        'terms',
        'data_sharing',
        'district_id',
        'entity_types',
    ]);
});

test('entity registration validates individual required fields correctly', function () {
    // Test legal_responsible_person is required
    $response = $this->post(route('entity.registration.submit'), [
        'name' => 'Test Entity',
        'legal_name' => 'Test Legal Name',
        // Missing legal_responsible_person
        'vat_number' => '123456789',
        'address' => 'Test Address',
        'location' => 'Test Location',
        'postal_code' => '1234-567',
        'email' => 'test@example.com',
        'phone' => '123456789',
        'user_email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'district_id' => $this->district->id,
        'entity_types' => ['sport'],
        'terms' => true,
        'data_sharing' => true,
    ]);

    $response->assertSessionHasErrors('legal_responsible_person');

    // Test vat_number is required
    $response = $this->post(route('entity.registration.submit'), [
        'name' => 'Test Entity',
        'legal_name' => 'Test Legal Name',
        'legal_responsible_person' => 'John Doe',
        // Missing vat_number
        'address' => 'Test Address',
        'location' => 'Test Location',
        'postal_code' => '1234-567',
        'email' => 'test@example.com',
        'phone' => '123456789',
        'user_email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'district_id' => $this->district->id,
        'entity_types' => ['sport'],
        'terms' => true,
        'data_sharing' => true,
    ]);

    $response->assertSessionHasErrors('vat_number');

    // Test address is required
    $response = $this->post(route('entity.registration.submit'), [
        'name' => 'Test Entity',
        'legal_name' => 'Test Legal Name',
        'legal_responsible_person' => 'John Doe',
        'vat_number' => '123456789',
        // Missing address
        'location' => 'Test Location',
        'postal_code' => '1234-567',
        'email' => 'test@example.com',
        'phone' => '123456789',
        'user_email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'district_id' => $this->district->id,
        'entity_types' => ['sport'],
        'terms' => true,
        'data_sharing' => true,
    ]);

    $response->assertSessionHasErrors('address');

    // Test postal_code is required
    $response = $this->post(route('entity.registration.submit'), [
        'name' => 'Test Entity',
        'legal_name' => 'Test Legal Name',
        'legal_responsible_person' => 'John Doe',
        'vat_number' => '123456789',
        'address' => 'Test Address',
        'location' => 'Test Location',
        // Missing postal_code
        'email' => 'test@example.com',
        'phone' => '123456789',
        'user_email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'district_id' => $this->district->id,
        'entity_types' => ['sport'],
        'terms' => true,
        'data_sharing' => true,
    ]);

    $response->assertSessionHasErrors('postal_code');

    // Test email is required
    $response = $this->post(route('entity.registration.submit'), [
        'name' => 'Test Entity',
        'legal_name' => 'Test Legal Name',
        'legal_responsible_person' => 'John Doe',
        'vat_number' => '123456789',
        'address' => 'Test Address',
        'location' => 'Test Location',
        'postal_code' => '1234-567',
        // Missing email
        'phone' => '123456789',
        'user_email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'district_id' => $this->district->id,
        'entity_types' => ['sport'],
        'terms' => true,
        'data_sharing' => true,
    ]);

    $response->assertSessionHasErrors('email');

    // Test phone is required
    $response = $this->post(route('entity.registration.submit'), [
        'name' => 'Test Entity',
        'legal_name' => 'Test Legal Name',
        'legal_responsible_person' => 'John Doe',
        'vat_number' => '123456789',
        'address' => 'Test Address',
        'location' => 'Test Location',
        'postal_code' => '1234-567',
        'email' => 'test@example.com',
        // Missing phone
        'user_email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'district_id' => $this->district->id,
        'entity_types' => ['sport'],
        'terms' => true,
        'data_sharing' => true,
    ]);

    $response->assertSessionHasErrors('phone');
});

test('entity registration validates GPS coordinates when provided', function () {
    $validData = [
        'name' => 'Test Entity',
        'legal_name' => 'Test Legal Name',
        'legal_responsible_person' => 'John Doe',
        'vat_number' => '123456789',
        'address' => 'Test Address',
        'location' => 'Test Location',
        'postal_code' => '1234-567',
        'email' => 'test@example.com',
        'phone' => '123456789',
        'user_email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'district_id' => $this->district->id,
        'entity_types' => ['sport'],
        'terms' => true,
        'data_sharing' => true,
        'lat' => 'invalid', // Invalid latitude
        'lng' => 'invalid', // Invalid longitude
    ];

    $response = $this->post(route('entity.registration.submit'), $validData);

    $response->assertSessionHasErrors(['lat', 'lng']);
});

test('entity registration form validates entity types selection', function () {
    $response = $this->post(route('entity.registration.submit'), [
        'name' => 'Test Entity',
        'legal_name' => 'Test Legal Name',
        'legal_responsible_person' => 'John Doe',
        'vat_number' => '123456789',
        'address' => 'Test Address',
        'location' => 'Test Location',
        'postal_code' => '1234-567',
        'email' => 'test@example.com',
        'phone' => '123456789',
        'user_email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'district_id' => $this->district->id,
        // Missing entity_types
        'terms' => true,
        'data_sharing' => true,
    ]);

    $response->assertSessionHasErrors('entity_types');
});

test('entity registration validates email format', function () {
    $response = $this->post(route('entity.registration.submit'), [
        'name' => 'Test Entity',
        'legal_name' => 'Test Legal Name',
        'legal_responsible_person' => 'John Doe',
        'vat_number' => '123456789',
        'address' => 'Test Address',
        'location' => 'Test Location',
        'postal_code' => '1234-567',
        'email' => 'invalid-email', // Invalid email format
        'phone' => '123456789',
        'user_email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'district_id' => $this->district->id,
        'entity_types' => ['sport'],
        'terms' => true,
        'data_sharing' => true,
    ]);

    $response->assertSessionHasErrors('email');
});

test('entity registration validates field length limits', function () {
    // Test vat_number max length (20 characters)
    $response = $this->post(route('entity.registration.submit'), [
        'name' => 'Test Entity',
        'legal_name' => 'Test Legal Name',
        'legal_responsible_person' => 'John Doe',
        'vat_number' => str_repeat('1', 21), // Exceeds 20 character limit
        'address' => 'Test Address',
        'location' => 'Test Location',
        'postal_code' => '1234-567',
        'email' => 'test@example.com',
        'phone' => '123456789',
        'user_email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'district_id' => $this->district->id,
        'entity_types' => ['sport'],
        'terms' => true,
        'data_sharing' => true,
    ]);

    $response->assertSessionHasErrors('vat_number');

    // Test postal_code max length (20 characters)
    $response = $this->post(route('entity.registration.submit'), [
        'name' => 'Test Entity',
        'legal_name' => 'Test Legal Name',
        'legal_responsible_person' => 'John Doe',
        'vat_number' => '123456789',
        'address' => 'Test Address',
        'location' => 'Test Location',
        'postal_code' => str_repeat('1', 21), // Exceeds 20 character limit
        'email' => 'test@example.com',
        'phone' => '123456789',
        'user_email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'district_id' => $this->district->id,
        'entity_types' => ['sport'],
        'terms' => true,
        'data_sharing' => true,
    ]);

    $response->assertSessionHasErrors('postal_code');
});
