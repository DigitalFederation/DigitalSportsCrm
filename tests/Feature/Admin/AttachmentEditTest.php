<?php

use App\Enums\UserGroupEnum;
use App\Models\Language;
use App\Models\User;
use Domain\Attachments\Models\Attachment;
use Domain\Attachments\Models\AttachmentCategory;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    DB::table('user_group')->updateOrInsert(
        ['id' => UserGroupEnum::ADMIN->value],
        ['code' => 'ADMIN', 'name' => 'Admin']
    );
});

it('allows admins to update attachment metadata', function (): void {
    $admin = User::factory()->create(['group_id' => UserGroupEnum::ADMIN->value]);
    $attachment = Attachment::factory()->create(['committee_id' => null]);
    $category = AttachmentCategory::factory()->create(['name' => 'Regulamento']);
    $language = Language::factory()->create(['name' => 'Portuguese']);

    $this->actingAs($admin)
        ->put(route('admin.attachments.update', $attachment), [
            'name' => 'Updated regulation',
            'category_id' => $category->id,
            'language_id' => $language->id,
        ])
        ->assertRedirect(route('admin.attachments.index'));

    $attachment->refresh();

    expect($attachment->name)->toBe('Updated regulation')
        ->and($attachment->category_id)->toBe($category->id)
        ->and($attachment->language_id)->toBe($language->id);
});
