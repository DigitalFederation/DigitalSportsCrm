<?php

namespace Domain\EvtEvents\Actions;

use Domain\Federations\Models\Federation;
use Illuminate\Http\UploadedFile;

class UpdateOrganizationEventHeroImageAction
{
    public function execute(UploadedFile $file): void
    {
        $federation = Federation::where('is_default_federation', true)->firstOrFail();
        $federation->clearMediaCollection('organization-event-hero');
        $federation->addMedia($file)->toMediaCollection('organization-event-hero');
    }
}
