<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('local');

    // Create admin user for EntityImport tests
    $group = Group::firstOrCreate(
        ['code' => 'ADMIN'],
        ['name' => 'Admin', 'description' => 'Admin Group']
    );

    Permission::firstOrCreate(['name' => 'access entities']);
    $role = Role::firstOrCreate(['name' => 'admin']);
    $role->givePermissionTo('access entities');

    $this->adminUser = User::factory()->create(['group_id' => $group->id]);
    $this->adminUser->assignRole('admin');
});

describe('DownloadExportExcelController path traversal protection', function () {

    test('rejects path traversal attempt with ../ sequence', function () {
        $this->actingAs($this->adminUser);

        $signedUrl = URL::signedRoute('download.excel.export', [
            'filePath' => '../../../.env',
            'fileName' => 'stolen.txt',
        ]);

        $response = $this->get($signedUrl);

        $response->assertStatus(403);
    });

    test('rejects path traversal attempt with encoded ..%2F sequence', function () {
        $this->actingAs($this->adminUser);

        // Attempt URL-encoded traversal (though Laravel will decode it)
        $signedUrl = URL::signedRoute('download.excel.export', [
            'filePath' => '..%2F..%2F..%2F.env',
            'fileName' => 'stolen.txt',
        ]);

        $response = $this->get($signedUrl);

        $response->assertStatus(403);
    });

    test('rejects absolute paths outside allowed directories', function () {
        $this->actingAs($this->adminUser);

        $signedUrl = URL::signedRoute('download.excel.export', [
            'filePath' => '/etc/passwd',
            'fileName' => 'passwd.txt',
        ]);

        $response = $this->get($signedUrl);

        $response->assertStatus(403);
    });

    test('rejects paths not starting with allowed directories', function () {
        $this->actingAs($this->adminUser);

        $signedUrl = URL::signedRoute('download.excel.export', [
            'filePath' => 'imports/entities/malicious.xlsx',
            'fileName' => 'file.xlsx',
        ]);

        $response = $this->get($signedUrl);

        $response->assertStatus(403);
    });

    test('rejects null byte injection attempts', function () {
        $this->actingAs($this->adminUser);

        $signedUrl = URL::signedRoute('download.excel.export', [
            'filePath' => "exports/file.xlsx\0.env",
            'fileName' => 'file.xlsx',
        ]);

        $response = $this->get($signedUrl);

        $response->assertStatus(403);
    });

    test('allows valid file path within exports directory', function () {
        $this->actingAs($this->adminUser);

        // Create a test file in the exports directory
        Storage::put('exports/test-report.xlsx', 'test content');

        $signedUrl = URL::signedRoute('download.excel.export', [
            'filePath' => 'exports/test-report.xlsx',
            'fileName' => 'test-report.xlsx',
        ]);

        $response = $this->get($signedUrl);

        $response->assertStatus(200);
    });

    test('allows valid file path within livewire-tmp directory', function () {
        $this->actingAs($this->adminUser);

        // Create a test file in the livewire-tmp directory
        Storage::put('livewire-tmp/test-upload.xlsx', 'test content');

        $signedUrl = URL::signedRoute('download.excel.export', [
            'filePath' => 'livewire-tmp/test-upload.xlsx',
            'fileName' => 'test-upload.xlsx',
        ]);

        $response = $this->get($signedUrl);

        $response->assertStatus(200);
    });

    test('returns 404 for non-existent file in allowed directory', function () {
        $this->actingAs($this->adminUser);

        $signedUrl = URL::signedRoute('download.excel.export', [
            'filePath' => 'exports/non-existent.xlsx',
            'fileName' => 'file.xlsx',
        ]);

        $response = $this->get($signedUrl);

        $response->assertStatus(404);
    });

    test('rejects empty file path', function () {
        $this->actingAs($this->adminUser);

        $signedUrl = URL::signedRoute('download.excel.export', [
            'filePath' => '',
            'fileName' => 'file.xlsx',
        ]);

        $response = $this->get($signedUrl);

        $response->assertStatus(403);
    });
});

describe('EntityImportController path traversal protection', function () {

    test('analyze endpoint rejects path traversal attempt', function () {
        $this->actingAs($this->adminUser);

        // Route uses GET with query parameter
        $response = $this->getJson(route('admin.entity.import.analyze', [
            'file_path' => '../../../.env',
        ]));

        $response->assertStatus(403)
            ->assertJson(['success' => false, 'error' => 'Invalid file path']);
    });

    test('analyze endpoint rejects paths outside imports/entities directory', function () {
        $this->actingAs($this->adminUser);

        $response = $this->getJson(route('admin.entity.import.analyze', [
            'file_path' => 'exports/report.xlsx',
        ]));

        $response->assertStatus(403)
            ->assertJson(['success' => false, 'error' => 'Invalid file path']);
    });

    test('validateMapping endpoint rejects path traversal attempt', function () {
        $this->actingAs($this->adminUser);

        // Route uses POST
        $response = $this->postJson(route('admin.entity.import.validate-mapping'), [
            'file_path' => '../../../.env',
            'field_mapping' => ['name' => 'name'],
        ]);

        $response->assertStatus(403)
            ->assertJson(['success' => false, 'error' => 'Invalid file path']);
    });

    test('preview endpoint rejects path traversal attempt', function () {
        $this->actingAs($this->adminUser);

        // Route uses GET with query parameters
        $response = $this->getJson(route('admin.entity.import.preview', [
            'file_path' => '../../../.env',
            'field_mapping' => ['name' => 'name'],
        ]));

        $response->assertStatus(403)
            ->assertJson(['success' => false, 'error' => 'Invalid file path']);
    });

    test('execute endpoint rejects path traversal attempt', function () {
        $this->actingAs($this->adminUser);

        // Route uses POST
        $response = $this->postJson(route('admin.entity.import.execute'), [
            'file_path' => '../../../.env',
            'field_mapping' => ['name' => 'name'],
        ]);

        $response->assertStatus(403)
            ->assertJson(['success' => false, 'error' => 'Invalid file path']);
    });

    test('analyze endpoint rejects null byte injection', function () {
        $this->actingAs($this->adminUser);

        $response = $this->getJson(route('admin.entity.import.analyze', [
            'file_path' => "imports/entities/file.csv\0.env",
        ]));

        $response->assertStatus(403)
            ->assertJson(['success' => false, 'error' => 'Invalid file path']);
    });

    test('analyze endpoint allows valid path in imports/entities directory', function () {
        $this->actingAs($this->adminUser);

        // Create a test file
        Storage::disk('local')->put('imports/entities/test.csv', "name\nTest Entity");

        $response = $this->getJson(route('admin.entity.import.analyze', [
            'file_path' => 'imports/entities/test.csv',
        ]));

        // Should either succeed or return 404 for file not found (not 403)
        $this->assertTrue(
            in_array($response->status(), [200, 404, 500]),
            'Expected status 200, 404, or 500 but got ' . $response->status()
        );

        if ($response->status() === 403) {
            $this->fail('Valid path was incorrectly rejected as invalid');
        }
    });
});
