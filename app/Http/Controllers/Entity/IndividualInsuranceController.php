<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use Domain\Insurance\Models\Insurance;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IndividualInsuranceController extends Controller
{
    public function index(): View
    {
        return view('web.entity.individual_insurances.index', [
            'insuranceOnly' => (bool) true,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:individual,id',
            'package_id' => 'required|exists:membership_packages,id',
            'insurance_plan_id' => 'required|exists:insurance_plans,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            DB::beginTransaction();

            // Check if the member belongs to the entity
            $entity = Auth::user()->getEntity();
            $member = $entity->individuals()->findOrFail($request->member_id);

            // Check validation plan privileges for entity member insurance requests
            $validationPlanService = resolve(ValidationPlanPrivilegeService::class);
            if (! $validationPlanService->canRequestInsurance($entity)) {
                $reason = $validationPlanService->getValidationPlanReason($entity, 'insurance');
                DB::rollBack();

                return back()->with('error', __('memberships.entity_member_insurance_not_authorized', ['reason' => $reason]));
            }

            // Create the insurance record
            $insurance = Insurance::create([
                'member_type' => $member->getMorphClass(),
                'member_id' => $member->id,
                'insurance_plan_id' => $request->insurance_plan_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status_class' => 'Domain\Insurance\States\ActiveInsuranceState',
                'requester_type' => $entity->getMorphClass(),
                'requester_id' => $entity->id,
                'request_type' => 'entity_group',
                'member_subscription_id' => null, // Insurance-only, not part of a subscription
            ]);

            DB::commit();

            return redirect()->route('entity.individual-insurances.index')
                ->with('success', __('Seguro associado com sucesso.'));

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', __('Ocorreu um erro. Vamos verificar o sucedido'))
                ->withInput();
        }
    }
}
