<?php

namespace App\Console\Commands;

use App\Jobs\GenerateModelQrCode;
use Illuminate\Console\Command;

class GenerateModelQrCodes extends Command
{
    protected $signature = 'qrcodes:generate {model}';
    protected $description = 'Generate QR codes for a specified model';

    protected $namespaceMap = [
        'entity' => 'Domain\\Entities\\Models\\Entity',
        'individual' => 'Domain\\Individuals\\Models\\Individual',
    ];

    public function handle()
    {
        $modelName = strtolower($this->argument('model'));

        if (! array_key_exists($modelName, $this->namespaceMap)) {
            $this->error("Unsupported model: $modelName");

            return;
        }

        $modelClass = $this->namespaceMap[$modelName];

        if (! class_exists($modelClass)) {
            $this->error("Model $modelClass not found.");

            return;
        }

        $model = app($modelClass);

        if (! method_exists($model, 'generateQrCode')) {
            $this->error("Model $modelClass does not support QR code generation.");

            return;
        }

        $model::whereNull($model->qrCodePathField())
            ->chunkById(100, function ($models) {

                foreach ($models as $model) {

                    GenerateModelQrCode::dispatch($model);

                }
            });

        $this->info("QR code generation jobs dispatched for $modelName.");
    }
}
