<?php

namespace App\Jobs;

use Domain\Individuals\Models\Individual;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IndividualQrCodeGenerate implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Individual $individual) {}

    public function handle(): void
    {
        $this->individual->generateQrCode();
    }
}
