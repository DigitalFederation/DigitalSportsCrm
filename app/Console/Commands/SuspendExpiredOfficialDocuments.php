<?php

namespace App\Console\Commands;

use Domain\OfficialDocuments\Actions\SuspendExpiredOfficialDocumentsAction;
use Illuminate\Console\Command;

class SuspendExpiredOfficialDocuments extends Command
{
    protected $signature = 'official-documents:suspend-expired';

    protected $description = 'Suspend official documents that have expired';

    public function handle(SuspendExpiredOfficialDocumentsAction $action)
    {
        $suspendedCount = $action->execute();
        $this->info("Suspended {$suspendedCount} expired official documents.");
    }
}
