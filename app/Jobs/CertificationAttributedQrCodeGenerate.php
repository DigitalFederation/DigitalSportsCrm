<?php

namespace App\Jobs;

use Domain\Certifications\Models\CertificationAttributed;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Support\QrCodeGenerator;

class CertificationAttributedQrCodeGenerate implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected CertificationAttributed $certification) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Generate the QR code and get the relative path
        $relativePath = QrCodeGenerator::generate($this->certification->international_code, 'qrcodes/certifications');

        // Save the relative path in the database
        $this->certification->qrcode_path = $relativePath;
        $this->certification->save();
    }
}
