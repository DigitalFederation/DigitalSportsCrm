<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! filter_var(config('seeding.default_admin.enabled', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->command?->warn('Default admin user was not seeded. Set SEED_DEFAULT_ADMIN=true and DEFAULT_ADMIN_PASSWORD to opt in.');

            return;
        }

        $password = config('seeding.default_admin.password');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('DEFAULT_ADMIN_PASSWORD must be set when SEED_DEFAULT_ADMIN=true.');
        }

        $email = (string) config('seeding.default_admin.email', 'admin@example.test');
        $name = (string) config('seeding.default_admin.name', 'admin');

        User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'group_id' => Group::where('code', 'ADMIN')->value('id'),
                'active' => true,
                'email_verified_at' => now(),
            ]
        )->assignRole('admin');
    }
}
