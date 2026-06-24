<?php

namespace App\Console\Commands;

use App\Jobs\CertificationAttributedQrCodeGenerate;
use Domain\Certifications\Models\CertificationAttributed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Throwable;

class GenerateCertificationAttributedQrCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr-code:certifications-generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a qr code for all certifications attributed who have not yet been generated';

    /**
     * Execute the console command.
     *
     * @throws Throwable
     */
    public function handle(): int
    {
        $certifications = CertificationAttributed::whereNull('qrcode_path')->get();
        $batch = Bus::batch([]);
        foreach ($certifications as $certification) {
            $batch->add(new CertificationAttributedQrCodeGenerate($certification));
        }
        $batch->dispatch();

        return 0;
    }
}
