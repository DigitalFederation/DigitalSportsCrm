<?php

use App\Enums\UserGroupEnum;
use App\Models\User;
use Domain\Documents\Models\Document;
use Domain\Invoicing\Models\MoloniInvoice;
use Domain\Invoicing\Services\MoloniInvoiceService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    DB::table('user_group')->updateOrInsert(
        ['id' => UserGroupEnum::ADMIN->value],
        ['code' => 'ADMIN', 'name' => 'Admin']
    );

    Permission::findOrCreate('access documents');
});

it('redirects authorized admin users to the Moloni PDF link', function (): void {
    $admin = User::factory()->create(['group_id' => UserGroupEnum::ADMIN->value]);
    $admin->givePermissionTo('access documents');
    $document = Document::factory()->create();

    MoloniInvoice::create([
        'document_id' => $document->id,
        'moloni_document_id' => 12345,
        'moloni_document_set_id' => 'A',
        'moloni_number' => 'FR 2026/1',
        'moloni_status' => 'closed',
        'moloni_total' => $document->total_value,
        'synced_at' => now(),
    ]);

    $this->mock(MoloniInvoiceService::class, function ($mock): void {
        $mock->shouldReceive('getPdfLink')
            ->once()
            ->with(12345)
            ->andReturn('https://moloni.test/invoice.pdf');
    });

    $this->actingAs($admin)
        ->get(route('admin.document.moloni-pdf', $document->id))
        ->assertRedirect('https://moloni.test/invoice.pdf');
});

it('returns not found when a document has no Moloni invoice', function (): void {
    $admin = User::factory()->create(['group_id' => UserGroupEnum::ADMIN->value]);
    $admin->givePermissionTo('access documents');
    $document = Document::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.document.moloni-pdf', $document->id))
        ->assertNotFound();
});
