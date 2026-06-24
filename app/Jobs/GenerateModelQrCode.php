<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateModelQrCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Model $model) {}

    public function handle(): void
    {
        if (method_exists($this->model, 'generateQrCode')) {
            $this->model->generateQrCode();
        } else {
            \Log::warning('Model ' . get_class($this->model) . ' does not have generateQrCode method.');
        }
    }
}
