<?php

namespace App\Livewire\Individual;

use App\Models\Committee;
use Domain\Federations\Models\Federation;
use Livewire\Component;

class PublicFormFederationChoice extends Component
{
    public $country_id;
    public $committee_id;
    public $main_federation_id;
    public $local_federation_id;

    public $countries = [];
    public $committees = [];
    public $main_federations = [];
    public $local_federations = [];

    public $has_federations = false;
    public $default_federation = null;
    public bool $useIndividualCommiteeNames = false;

    public function mount($countries, $useIndividualCommiteeNames = false)
    {
        $this->countries = $countries;
        $this->useIndividualCommiteeNames = $useIndividualCommiteeNames;

        // Load committees with conditional name transformation
        $this->committees = Committee::orderBy('id', 'desc')
            ->get()
            ->mapWithKeys(function ($committee) {
                $name = $this->useIndividualCommiteeNames
                    ? $committee->getIndividualDisplayName()
                    : $committee->name;

                return [$committee->id => $name];
            })
            ->toArray();
    }

    public function updatedCountryId($value)
    {
        $this->reset(['committee_id', 'main_federation_id', 'local_federation_id', 'main_federations', 'local_federations']);

        if ($value) {
            // Check if country has any active federations
            $hasActiveFederations = Federation::where('country_id', $value)
                ->whereNull('parent_id')
                ->whereHas('memberships', function ($query) {
                    $query->whereHas('plans');
                })
                ->exists();

            if (! $hasActiveFederations) {
                // Get default federation
                $this->default_federation = Federation::where('is_default_federation', true)
                    ->first();

                if ($this->default_federation) {
                    $this->main_federation_id = $this->default_federation->id;
                    $this->has_federations = false;
                }
            } else {
                $this->has_federations = true;
                $this->default_federation = null;
            }
        }
    }

    public function updatedCommitteeId($value)
    {
        if ($value && $this->country_id) {
            // Get federations that have membership plans for the selected committee
            $this->main_federations = Federation::where('country_id', $this->country_id)
                ->whereNull('parent_id')
                ->whereHas('memberships', function ($query) {
                    $query->whereHas('plans', function ($query) {
                        $query->where('committee_id', $this->committee_id);
                    });
                })
                ->pluck('name', 'id')
                ->toArray();

            // If no federations found for this committee, get default federation
            if (empty($this->main_federations)) {
                $this->default_federation = Federation::where('is_default_federation', true)
                    ->first();

                if ($this->default_federation) {
                    $this->main_federation_id = $this->default_federation->id;
                    $this->has_federations = false;
                }
            } else {
                $this->has_federations = true;
                $this->default_federation = null;
            }
        }

        $this->reset(['main_federation_id', 'local_federation_id', 'local_federations']);
    }

    public function updatedMainFederationId($value)
    {
        if ($value) {
            // Query local federations that either:
            // 1. Have direct memberships with plans for the committee
            // 2. Have local membership plan associations for the committee
            $query = Federation::where('parent_id', $value)
                ->where(function ($query) {
                    $query->whereHas('memberships', function ($query) {
                        $query->whereHas('plans', function ($query) {
                            $query->where('committee_id', $this->committee_id);
                        });
                    })
                        ->orWhereHas('localMembershipPlan', function ($query) {
                            $query->whereHas('membershipPlan', function ($query) {
                                $query->where('committee_id', $this->committee_id);
                            });
                        });
                });

            $this->local_federations = $query->pluck('name', 'id')->toArray();
        } else {
            $this->reset(['local_federation_id', 'local_federations']);
        }
    }

    public function render()
    {
        return view('livewire.individual.public-federation-choice', [
            'show_federation_selection' => $this->has_federations,
        ]);
    }
}
