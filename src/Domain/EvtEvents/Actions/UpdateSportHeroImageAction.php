<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Sport;
use Illuminate\Http\UploadedFile;

class UpdateSportHeroImageAction
{
    public function execute(Sport $sport, UploadedFile $file): void
    {
        $sport->clearMediaCollection('hero-image');
        $sport->addMedia($file)->toMediaCollection('hero-image');
    }
}
