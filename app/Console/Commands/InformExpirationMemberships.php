<?php

namespace App\Console\Commands;

use App\Jobs\InformExpirationMembershipsJob;
use Illuminate\Console\Command;

class InformExpirationMemberships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'membership:inform-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inform users that their memberships will expire';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $time = $this->ask('What is the time you want to inform?', '1 month');
        dispatch(new InformExpirationMembershipsJob($time));
        $this->info('Users with expired memberships was been informed.');
    }
}
