<?php

use App\Models\Group;
use App\Models\User;
use Domain\Imports\Models\Import;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Queue::fake();
    Storage::fake('local');

    // Create the ADMIN group if it doesn't exist
    $group = Group::firstOrCreate(
        ['code' => 'ADMIN'],
        ['name' => 'Admin', 'description' => 'Admin Group']
    );

    // Create the permission if it doesn't exist
    Permission::firstOrCreate(['name' => 'access individuals']);

    // Create the admin role if it doesn't exist
    $role = Role::firstOrCreate(['name' => 'admin']);
    $role->givePermissionTo('access individuals');

    $this->user = User::factory()->create(['group_id' => $group->id]);
    $this->user->assignRole('admin');
    $this->actingAs($this->user);
});

test('import page requires authentication', function () {
    auth()->logout();

    $response = $this->get(route('admin.individual.import.index'));

    $response->assertRedirect('/login');
});

test('import page displays correctly', function () {
    $response = $this->get(route('admin.individual.import.index'));

    $response->assertOk()
        ->assertViewIs('web.admin.individual.import.index')
        ->assertViewHas('federations')
        ->assertViewHas('supportedFields');
});

test('file upload validates correctly', function () {
    $invalidFile = UploadedFile::fake()->create('test.txt', 100);

    $response = $this->postJson(route('admin.individual.import.upload'), [
        'file' => $invalidFile,
        'step' => 'upload',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('file');
});

test('file upload accepts valid files', function () {
    // Create a CSV file with actual content
    $csvContent = "Name,Email,Birthdate\nJohn Doe,john@example.com,1990-01-01";
    $csvFile = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

    $response = $this->postJson(route('admin.individual.import.upload'), [
        'file' => $csvFile,
        'step' => 'upload',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'next_step' => 'mapping',
        ]);
});

test('progress endpoint returns import status', function () {
    $import = Import::create([
        'user_id' => $this->user->id,
        'type' => 'individual',
        'filename' => 'test.csv',
        'file_path' => 'imports/test.csv',
        'status' => 'processing',
        'total_rows' => 100,
        'processed_rows' => 25,
        'success_count' => 20,
        'error_count' => 5,
        'field_mapping' => [],
        'options' => [],
    ]);

    $response = $this->get(route('admin.individual.import.progress', $import->id));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'status' => 'processing',
            'total_rows' => 100,
            'processed_rows' => 25,
            'percentage' => 25,
            'success_count' => 20,
            'error_count' => 5,
        ]);
});

test('progress endpoint denies access to other users imports', function () {
    $otherUser = User::factory()->create();
    $import = Import::create([
        'user_id' => $otherUser->id,
        'type' => 'individual',
        'filename' => 'test.csv',
        'file_path' => 'imports/test.csv',
        'status' => 'processing',
        'field_mapping' => [],
        'options' => [],
    ]);

    $response = $this->get(route('admin.individual.import.progress', $import->id));

    $response->assertNotFound();
});

test('cancel endpoint cancels import', function () {
    $import = Import::create([
        'user_id' => $this->user->id,
        'type' => 'individual',
        'filename' => 'test.csv',
        'file_path' => 'imports/test.csv',
        'status' => 'processing',
        'field_mapping' => [],
        'options' => [],
    ]);

    $response = $this->postJson(route('admin.individual.import.cancel', $import->id));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Import cancelled successfully',
        ]);

    expect($import->fresh()->status)->toBe('cancelled');
});

test('download template returns csv file', function () {
    $response = $this->get(route('admin.individual.import.template'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=utf-8')
        ->assertHeader('Content-Disposition', 'attachment; filename="individual_import_template.csv"');
});
