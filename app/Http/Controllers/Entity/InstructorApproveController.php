<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\CanceledEntityProfessionalRoleState;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class InstructorApproveController extends Controller
{
    public function update(int $associationId, bool $answer): RedirectResponse
    {
        $entityInstructor = EntityProfessionalRole::findOrFail($associationId);
        try {
            $entityInstructor->update(['status_class' => $answer ? ActiveEntityProfessionalRoleState::class : CanceledEntityProfessionalRoleState::class]);

        } catch (Exception $e) {
            Log::error($e->getCode().': '.$e->getMessage());
            abort(500, 'Error answering the invitation to be an instructor.');
        }

        return redirect('/')->with('success', 'Answered the invitation to be an instructor.');
    }
}
