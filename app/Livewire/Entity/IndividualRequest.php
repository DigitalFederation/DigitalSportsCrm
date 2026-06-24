<?php

namespace App\Livewire\Entity;

use Domain\Individuals\Actions\CreateIndividualEntityAction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class IndividualRequest extends Component
{
    public $member_code;

    public $member_number;

    protected function rules(): array
    {
        return [
            'member_code' => 'nullable|required_without:member_number|exists:individual,member_code',
            'member_number' => 'nullable|required_without:member_code|exists:individual,member_number',
        ];
    }

    protected function messages(): array
    {
        return [
            'member_code.required_without' => __('validation.required', ['attribute' => __('main.personal_id')]),
            'member_code.exists' => __('entity.invalid_member_code'),
            'member_number.required_without' => __('validation.required', ['attribute' => __('entity.member_number')]),
            'member_number.exists' => __('entity.invalid_member_number'),
        ];
    }

    public function submit(CreateIndividualEntityAction $action): mixed
    {
        $this->validate();

        try {
            $doInvite = $action->execute($this->member_code, Auth::user()->getEntityId(), $this->member_number);

            // Reset fields
            $this->member_code = '';
            $this->member_number = '';

            if (empty($doInvite)) {
                // Redirect with error - closes modal, shows message in main view
                return redirect()->route('entity.individual.index')
                    ->with('error', __('entity.member_must_have_federation'));
            }

            // Redirect with success - closes modal, shows message in main view
            return redirect()->route('entity.individual.index')
                ->with('success', __('entity.invitation_sent_success'));
        } catch (\Exception $ex) {
            // For exceptions, stay in modal to show error
            session()->flash('error', __('entity.error_creating_record', ['error' => $ex->getMessage()]));
            \Log::error($ex->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.entity.individual-request');
    }
}
