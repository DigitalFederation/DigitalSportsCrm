<?php

namespace App\Livewire;

use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\TechnicalDelegate;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Livewire\Component;

class EventTechnicalDelegateForm extends Component
{
    public string $name = '';

    public ?int $federationId = null;

    public string $memberCodeDelegateFederation = '';

    public string $appointmentByBodNumber = '';

    public ?string $dateOfBodAppointment = null;

    public ?int $competitionId = null;

    public ?string $individualId = null;

    public $federations;

    protected function rules()
    {
        return [
            'name' => 'required|string',
            'federationId' => 'required|integer',
            'memberCodeDelegateFederation' => 'required|string|exists:individual,member_code',
            'dateOfBodAppointment' => 'required|date',
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'federationId.required' => 'Please select a Federation.',
            'federationId.integer' => 'The federation ID must be an integer.',
            'memberCodeDelegateFederation.required' => 'Please enter a valid CMAS code.',
            'memberCodeDelegateFederation.exists' => 'The CMAS code must exist in our records.',
            'dateOfBodAppointment.required' => 'Please enter a bod appointment date',
        ];
    }

    public function mount(Competition $competition): void
    {
        $this->initializeFederations();
        $this->initializeDelegate($competition);
    }

    private function initializeFederations()
    {
        $this->federations = Federation::all()->pluck('name', 'id');
    }

    private function initializeDelegate(Competition $competition)
    {
        $this->competitionId = $competition->id;
        $delegate = TechnicalDelegate::where('competition_id', $competition->id)->first();

        if (! empty($delegate)) {
            $this->name = $delegate->name;
            $this->federationId = $delegate->federation_id;
            $this->memberCodeDelegateFederation = $delegate->member_code_delegate_federation;
            $this->appointmentByBodNumber = $delegate->appointment_by_bod_number;
            $this->dateOfBodAppointment = $delegate->date_of_bod_appointment;
            $this->individualId = $delegate->individual_id;
        }
    }

    private function fetchOrAssignIndividualId()
    {
        if (empty($this->individualId)) {
            $this->individualId = Individual::where('member_code', $this->memberCodeDelegateFederation)->firstOrFail()->id;
        }
    }

    public function save()
    {
        $this->validate();

        $this->fetchOrAssignIndividualId();

        $delegateData = [
            'name' => $this->name,
            'federation_id' => $this->federationId,
            'member_code_delegate_federation' => $this->memberCodeDelegateFederation,
            'appointment_by_bod_number' => $this->appointmentByBodNumber,
            'date_of_bod_appointment' => $this->dateOfBodAppointment,
            'individual_id' => $this->individualId,
        ];

        TechnicalDelegate::updateOrCreate(
            ['competition_id' => $this->competitionId],
            $delegateData
        );

        session()->flash('success', __('Technical Delegate added successfully.'));

        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.technical-delegate-form');
    }
}
