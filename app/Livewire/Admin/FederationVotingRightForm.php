<?php

namespace App\Livewire\Admin;

use Domain\Federations\Models\Federation;
use Domain\Federations\Models\FederationVotingRight;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FederationVotingRightForm extends Component
{
    public int $federationId;
    public int $year;
    public ?FederationVotingRight $votingRight = null; // Holds the model instance
    public Federation $federation; // Hold the federation details

    // Form state properties
    public string $general_assembly_status;
    public string $technical_committee_status;
    public string $scientific_committee_status;
    public string $sport_committee_status;
    public string $finswimming_commission_status;
    public string $freediving_commission_status;
    public string $aquathlon_commission_status;
    public string $underwater_hockey_commission_status;
    public string $underwater_rugby_commission_status;
    public string $target_shooting_commission_status;
    public string $sport_diving_commission_status;
    public string $spearfishing_commission_status;
    public string $orienteering_commission_status;
    public string $visual_commission_status;

    public array $statusOptions = [];

    protected function rules(): array
    {
        $statusRule = Rule::in(FederationVotingRight::STATUS_OPTIONS);

        return [
            'general_assembly_status' => ['required', $statusRule],
            'technical_committee_status' => ['required', $statusRule],
            'scientific_committee_status' => ['required', $statusRule],
            'sport_committee_status' => ['required', $statusRule],
            'finswimming_commission_status' => ['required', $statusRule],
            'freediving_commission_status' => ['required', $statusRule],
            'aquathlon_commission_status' => ['required', $statusRule],
            'underwater_hockey_commission_status' => ['required', $statusRule],
            'underwater_rugby_commission_status' => ['required', $statusRule],
            'target_shooting_commission_status' => ['required', $statusRule],
            'sport_diving_commission_status' => ['required', $statusRule],
            'spearfishing_commission_status' => ['required', $statusRule],
            'orienteering_commission_status' => ['required', $statusRule],
            'visual_commission_status' => ['required', $statusRule],
        ];
    }

    public function mount(int $federationId, int $year): void
    {
        $this->federationId = $federationId;
        $this->year = $year;
        $this->federation = Federation::findOrFail($this->federationId);
        $this->statusOptions = FederationVotingRight::STATUS_OPTIONS;

        $this->votingRight = FederationVotingRight::firstOrNew(
            [
                'federation_id' => $this->federationId,
                'year' => $this->year,
            ]
        );

        // Initialize form state
        $this->general_assembly_status = $this->votingRight->general_assembly_status ?? FederationVotingRight::STATUS_NO_VOTING_RIGHT;
        $this->technical_committee_status = $this->votingRight->technical_committee_status ?? FederationVotingRight::STATUS_NO_VOTING_RIGHT;
        $this->scientific_committee_status = $this->votingRight->scientific_committee_status ?? FederationVotingRight::STATUS_NO_VOTING_RIGHT;
        $this->sport_committee_status = $this->votingRight->sport_committee_status ?? FederationVotingRight::STATUS_NO_VOTING_RIGHT;
        $this->finswimming_commission_status = $this->votingRight->finswimming_commission_status ?? FederationVotingRight::STATUS_NO_VOTING_RIGHT;
        $this->freediving_commission_status = $this->votingRight->freediving_commission_status ?? FederationVotingRight::STATUS_NO_VOTING_RIGHT;
        $this->aquathlon_commission_status = $this->votingRight->aquathlon_commission_status ?? FederationVotingRight::STATUS_NO_VOTING_RIGHT;
        $this->underwater_hockey_commission_status = $this->votingRight->underwater_hockey_commission_status ?? FederationVotingRight::STATUS_NO_VOTING_RIGHT;
        $this->underwater_rugby_commission_status = $this->votingRight->underwater_rugby_commission_status ?? FederationVotingRight::STATUS_NO_VOTING_RIGHT;
        $this->target_shooting_commission_status = $this->votingRight->target_shooting_commission_status ?? FederationVotingRight::STATUS_NO_VOTING_RIGHT;
        $this->sport_diving_commission_status = $this->votingRight->sport_diving_commission_status ?? FederationVotingRight::STATUS_NO_VOTING_RIGHT;
        $this->spearfishing_commission_status = $this->votingRight->spearfishing_commission_status ?? FederationVotingRight::STATUS_NO_VOTING_RIGHT;
        $this->orienteering_commission_status = $this->votingRight->orienteering_commission_status ?? FederationVotingRight::STATUS_NO_VOTING_RIGHT;
        $this->visual_commission_status = $this->votingRight->visual_commission_status ?? FederationVotingRight::STATUS_NO_VOTING_RIGHT;
    }

    public function save(): void
    {
        $validatedData = $this->validate();
        $validatedData['year'] = $this->year;
        $validatedData['federation_id'] = $this->federationId;

        // Log the data just before the database operation
        Log::debug('Saving FederationVotingRight:', [
            'conditions' => [
                'federation_id' => $this->federationId,
                'year' => $this->year,
            ],
            'data_to_save' => $validatedData,
        ]);

        try {
            FederationVotingRight::updateOrCreate(
                [
                    'federation_id' => $this->federationId,
                    'year' => $this->year,
                ],
                $validatedData
            );
        } catch (\Illuminate\Database\QueryException $e) {
            // Log the detailed error when the query fails
            Log::error('QueryException during FederationVotingRight save', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'previous' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null,
                'conditions_used' => ['federation_id' => $this->federationId, 'year' => $this->year],
                'data_attempted' => $validatedData,
            ]);
            Notification::make()
                ->title(__('common.error'))
                ->body(__('federation.voting_rights_database_error', ['code' => $e->getCode()]))
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title(__('common.success'))
            ->body(__('federation.voting_rights_updated_successfully'))
            ->success()
            ->send();

        $this->dispatch('votingRightsSaved');
    }

    public function cancel(): void
    {
        $this->dispatch('cancelVotingRightsEdit');
    }

    public function render()
    {
        return view('livewire.admin.federation-voting-right-form');
    }
}
