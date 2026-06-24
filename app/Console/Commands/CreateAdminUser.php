<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'user:create-admin';

    protected $description = 'Creates a new admin user with admin capabilities';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = trim((string) $this->ask('What is the user\'s name?'));
        $email = trim((string) $this->ask('What is the user\'s email?'));
        $password = (string) $this->secret('What is the user\'s password?');
        $passwordConfirmation = (string) $this->secret('Confirm the user\'s password');

        if ($name === '') {
            $this->error('Name is required.');

            return self::FAILURE;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid email address is required.');

            return self::FAILURE;
        }

        if ($password === '') {
            $this->error('Password is required.');

            return self::FAILURE;
        }

        if ($password !== $passwordConfirmation) {
            $this->error('Password confirmation does not match.');

            return self::FAILURE;
        }

        $group = Group::where('code', 'ADMIN')->firstOrFail();
        // Check if the user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("A user with the email {$email} already exists.");

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'group_id' => $group->id,
            'active' => true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole('admin');

        $this->info("User {$name} created successfully with admin capabilities.");

        return self::SUCCESS;
    }
}
