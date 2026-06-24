<?php

use App\Enums\UserGroupEnum;
use App\Livewire\Admin\BackupManager;
use App\Livewire\Admin\BackupSettings;
use App\Models\BackupSetting;
use App\Models\Group;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Group::query()->delete();
    Group::insert([
        ['id' => 1, 'name' => 'Individual', 'code' => 'INDIVIDUAL'],
        ['id' => 2, 'name' => 'Entity', 'code' => 'ENTITY'],
        ['id' => 3, 'name' => 'Federation', 'code' => 'FEDERATION'],
        ['id' => 5, 'name' => 'Admin', 'code' => 'ADMIN'],
    ]);

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'access backups', 'guard_name' => 'web']);

    $this->adminUser = User::factory()->create([
        'group_id' => UserGroupEnum::ADMIN->value,
    ]);
    $this->adminUser->assignRole('admin');
    $this->adminUser->givePermissionTo('access backups');

    $this->regularUser = User::factory()->create([
        'group_id' => UserGroupEnum::INDIVIDUAL->value,
    ]);

    Storage::fake('r2-backups');
});

it('allows admin with permission to access backup index page', function () {
    actingAs($this->adminUser);

    $this->get(route('admin.backups.index'))
        ->assertSuccessful()
        ->assertSee(__('backups.title'));
});

it('denies access to users without admin role', function () {
    actingAs($this->regularUser);

    $this->get(route('admin.backups.index'))
        ->assertForbidden();
});

it('denies access to admin without access backups permission', function () {
    $adminWithoutPermission = User::factory()->create([
        'group_id' => UserGroupEnum::ADMIN->value,
    ]);
    $adminWithoutPermission->assignRole('admin');

    actingAs($adminWithoutPermission);

    $this->get(route('admin.backups.index'))
        ->assertForbidden();
});

it('renders the backup manager livewire component', function () {
    actingAs($this->adminUser);

    Livewire::test(BackupManager::class)
        ->assertSuccessful()
        ->assertSee(__('backups.create_backup'));
});

it('shows empty state when no backups exist', function () {
    actingAs($this->adminUser);

    Livewire::test(BackupManager::class)
        ->assertSee(__('backups.no_backups'));
});

it('can trigger a backup creation', function () {
    actingAs($this->adminUser);

    Queue::fake();
    RateLimiter::clear('backup-create:' . $this->adminUser->id);

    Livewire::test(BackupManager::class)
        ->call('createBackup')
        ->assertHasNoErrors();

    Queue::assertPushed(\Illuminate\Queue\CallQueuedClosure::class);
});

it('rate limits backup creation', function () {
    actingAs($this->adminUser);

    Queue::fake();
    $rateLimitKey = 'backup-create:' . $this->adminUser->id;
    RateLimiter::clear($rateLimitKey);

    // First call should succeed
    Livewire::test(BackupManager::class)
        ->call('createBackup')
        ->assertHasNoErrors();

    // Second call should be rate limited (notification sent but no queue push)
    Livewire::test(BackupManager::class)
        ->call('createBackup');

    // Only one job should be queued (the first one)
    Queue::assertPushed(\Illuminate\Queue\CallQueuedClosure::class, 1);
});

it('allows admin to download a backup file', function () {
    actingAs($this->adminUser);

    Storage::disk('r2-backups')->put('test-backup.zip', 'fake-backup-content');

    $this->get(route('admin.backups.download', 'test-backup.zip'))
        ->assertSuccessful();
});

it('returns 404 for non-existent backup file', function () {
    actingAs($this->adminUser);

    $this->get(route('admin.backups.download', 'non-existent.zip'))
        ->assertNotFound();
});

it('rejects path traversal filenames via route constraint', function () {
    actingAs($this->adminUser);

    // These should not match the route pattern [a-zA-Z0-9\-\_\.]+
    $this->get('/admin/backups/../../../etc/passwd/download')
        ->assertNotFound();

    $this->get('/admin/backups/..%2F..%2Fetc%2Fpasswd/download')
        ->assertNotFound();
});

it('lists backups from storage disk', function () {
    Storage::disk('r2-backups')->put('backup-2024-01-01.zip', 'content1');
    Storage::disk('r2-backups')->put('backup-2024-01-02.zip', 'content2');

    $service = new BackupService;
    $backups = $service->listBackups();

    expect($backups)->toHaveCount(2)
        ->and($backups->first()['name'])->toContain('.zip');
});

it('returns correct stats', function () {
    Storage::disk('r2-backups')->put('backup-2024-01-01.zip', str_repeat('a', 1024));
    Storage::disk('r2-backups')->put('backup-2024-01-02.zip', str_repeat('b', 2048));

    $service = new BackupService;
    $stats = $service->getStats();

    expect($stats['total_count'])->toBe(2)
        ->and($stats['total_size'])->toBe(3072)
        ->and($stats['last_backup'])->not->toBeNull();
});

it('returns null last_backup when no backups exist', function () {
    $service = new BackupService;
    $stats = $service->getStats();

    expect($stats['total_count'])->toBe(0)
        ->and($stats['total_size'])->toBe(0)
        ->and($stats['last_backup'])->toBeNull();
});

// --- Backup Settings Tests ---

it('renders the backup settings livewire component', function () {
    actingAs($this->adminUser);

    Livewire::test(BackupSettings::class)
        ->assertSuccessful()
        ->assertSee(__('backups.settings_save'));
});

it('loads default values when no settings exist in database', function () {
    actingAs($this->adminUser);

    Livewire::test(BackupSettings::class)
        ->assertSet('backupEnabled', true)
        ->assertSet('backupFrequency', 'daily')
        ->assertSet('backupTime', '02:00')
        ->assertSet('retentionDays', 30)
        ->assertSet('maxStorageMb', 5000);
});

it('allows admin to save backup settings', function () {
    actingAs($this->adminUser);

    Livewire::test(BackupSettings::class)
        ->set('backupEnabled', false)
        ->set('backupFrequency', 'weekly')
        ->set('backupTime', '04:30')
        ->set('retentionDays', 60)
        ->set('maxStorageMb', 10000)
        ->call('save')
        ->assertHasNoErrors();

    expect(BackupSetting::getValue('backup_enabled'))->toBeFalse()
        ->and(BackupSetting::getValue('backup_frequency'))->toBe('weekly')
        ->and(BackupSetting::getValue('backup_time'))->toBe('04:30')
        ->and(BackupSetting::getValue('retention_days'))->toBe(60)
        ->and(BackupSetting::getValue('max_storage_mb'))->toBe(10000);
});

it('persists settings across component re-renders', function () {
    actingAs($this->adminUser);

    BackupSetting::setValue('backup_frequency', 'weekly');
    BackupSetting::setValue('backup_time', '06:00');
    BackupSetting::setValue('retention_days', 90);

    Livewire::test(BackupSettings::class)
        ->assertSet('backupFrequency', 'weekly')
        ->assertSet('backupTime', '06:00')
        ->assertSet('retentionDays', 90);
});

it('rejects invalid setting values', function (string $field, mixed $value) {
    actingAs($this->adminUser);

    Livewire::test(BackupSettings::class)
        ->set($field, $value)
        ->call('save')
        ->assertHasErrors([$field]);
})->with([
    'invalid frequency' => ['backupFrequency', 'invalid_frequency'],
    'invalid time format' => ['backupTime', '25:99'],
    'retention days too low' => ['retentionDays', 0],
    'retention days too high' => ['retentionDays', 500],
    'max storage too low' => ['maxStorageMb', 50],
]);

it('denies backup settings access to users without permission', function () {
    actingAs($this->regularUser);

    Livewire::test(BackupSettings::class)
        ->assertForbidden();
});
