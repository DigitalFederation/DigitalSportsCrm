<?php

namespace App\Console\Commands;

use App\Jobs\IndividualQrCodeGenerate;
use Domain\Individuals\Models\Individual;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Throwable;

class GenerateIndividualsQrCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr-code:individuals-generate {--force : Regenerate all QR codes, even if they already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a qr code for all individuals who have not yet been generated';

    /**
     * Execute the console command.
     *
     * @throws Throwable
     */
    public function handle(): int
    {
        $query = Individual::query();

        if (! $this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('qrcode_path')
                    ->orWhere('qrcode_path', '');
            });
        }

        $individuals = $query->get();

        $batch = Bus::batch([]);
        foreach ($individuals as $individual) {
            $batch->add(new IndividualQrCodeGenerate($individual));
        }
        $batch->dispatch();

        return 0;
    }
}
