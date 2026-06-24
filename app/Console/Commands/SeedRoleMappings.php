<?php

namespace App\Console\Commands;

use Database\Seeders\RoleMappingSeeder;
use Illuminate\Console\Command;

class SeedRoleMappings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:role-mappings 
                            {--fresh : Drop existing mappings before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed role mapping pivot tables (license_roles, certification_roles, federation_roles)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting role mapping seeder...');

        if ($this->option('fresh')) {
            $this->warn('Dropping existing role mappings...');

            if ($this->confirm('This will delete all existing role mappings. Are you sure?')) {
                \DB::table('license_roles')->truncate();
                \DB::table('certification_roles')->truncate();
                \DB::table('federation_roles')->whereNull('federation_id')->delete();
                $this->info('Existing mappings deleted.');
            } else {
                $this->info('Operation cancelled.');

                return;
            }
        }

        // Run the seeder
        $seeder = new RoleMappingSeeder;
        $seeder->setCommand($this);
        $seeder->run();

        $this->newLine();
        $this->info('Role mapping seeder completed!');

        // Show some statistics
        $this->table(
            ['Table', 'Count'],
            [
                ['license_roles', \DB::table('license_roles')->count()],
                ['certification_roles', \DB::table('certification_roles')->count()],
                ['federation_roles (NULL federation)', \DB::table('federation_roles')->whereNull('federation_id')->count()],
            ]
        );
    }
}
