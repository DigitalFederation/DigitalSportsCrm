<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Traits\StreamsMediaFromStorage;
use Domain\Insurance\Models\InsurancePlan;

class InsurancePlanController extends Controller
{
    use StreamsMediaFromStorage;

    public function downloadAttachment($id, $mediaId)
    {
        $insurancePlan = InsurancePlan::findOrFail($id);
        $media = $insurancePlan->getMedia('insurance_attachments')->where('id', $mediaId)->firstOrFail();

        // Check if the user is authorized to access this file
        // $this->authorize('download', $insurancePlan);

        return $this->streamMediaDownload($media, $media->file_name);
    }
}
