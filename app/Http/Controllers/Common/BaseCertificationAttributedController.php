<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\CertificationAttributedRequest;
use Domain\Certifications\Actions\CreateCertificationAttributedAction;
use Domain\Certifications\DataTransferObject\CertificationAttributedData;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BaseCertificationAttributedController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(
        CertificationAttributedRequest $request,
        CreateCertificationAttributedAction $saveAction
    ): RedirectResponse {

        try {
            DB::beginTransaction();
            $save = $saveAction(CertificationAttributedData::fromArray($request));
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());

            return back()->with('error', 'There was a problem while creating this record: ' . $ex->getMessage());
        }

        $message = 'Certification attributed with success.';
        foreach ($save['individualsWithTheCertification'] as $key => $individual) {

            if ($key == 0) {
                $message .= ' This individual already has this certification(s): ';
            }

            $message .= $individual;
            if (count($save['individualsWithTheCertification']) > $key + 1) {
                $message .= ', ';
            }
        }

        return redirect()->back()->with('success', $message);
    }
}
