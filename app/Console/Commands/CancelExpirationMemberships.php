<?php

namespace App\Console\Commands;

use App\Jobs\CancelExpirationMembershipsJob;
use Illuminate\Console\Command;

class CancelExpirationMemberships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memberships:cancel-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change the state of all memberships that the current term ends date is past.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        dispatch(new CancelExpirationMembershipsJob);
        $this->info('Memberships canceled successfully.');
    }
}
