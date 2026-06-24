<?php

namespace App\Livewire;

use Domain\Federations\Models\Federation;
use Domain\Memberships\States\ActiveMembershipState;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * PublicIndividualFederationChoice Class
 *
 * This class is a Livewire component, responsible for allowing the user to choose a federation and country.
 * The choices made here will affect the federations and memberships available to the user in the subsequent steps.
 *
 * @property array $federations - Contains federations available to the user for selection.
 * @property array $federation_id_array - Contains user's federation choices.
 * @property array $local_federation_id_array - Contains user's choices related to local federation.
 * @property array $local_membership_plan_array - Contains the membership plan IDs that correspond to the user's chosen federations.
 * @property Collection|null $local_federations - Contains local federations based on user's chosen federations.
 * @property int|null $local_federation_id - Contains the user's chosen local federation id.
 * @property Collection|null $countries - Contains countries available to the user for selection.
 * @property int|null $country_id - Contains the user's chosen country id.
 * @property bool $federations_filtered_by_committee - Flag to check if federations are filtered by committee.
 * @property string|null $error_message - Contains the error message if any error occurs.
 *
 * @method void updated(string $name) - Triggers when any property defined in the class gets updated. It will reset and set the local membership plan array based on user's federation choice, also it will update the local federations.
 * @method void setLocalMembershipPlanArray() - Set the membership plan ids corresponding to the user's federation choice.
 * @method void resetLocalMembershipPlanArray() - Reset the local membership plan array.
 * @method void render() - Lifecycle hook of Livewire that renders the view.
 * @method void getFederationsByCountry() - Fetch the federations by country.
 * @method void getLocalFederations() - Fetch the local federations based on the user's federation choice.
 */
class PublicIndividualFederationChoice extends Component
{
    public $federations;

    public $federation_id_array = []; // choice

    public $local_federation_id_array = []; // choice

    public $local_membership_plan_array = []; // choice hidden for local filtering

    public $local_federations;

    public $local_federation_id; // choice

    public $countries;

    public $country_id; // choice

    public $federations_filtered_by_committee = false;

    public $error_message;

    public function updated($name)
    {
        if (Str::startsWith($name, 'federation_id_array')) {
            $index = Str::after($name, 'federation_id_array.');

            $this->resetLocalMembershipPlanArray();
            $this->setLocalMembershipPlanArray();
            $this->getLocalFederations();
        }
    }

    /**
     * Tracking the state changes directly, we should derive the membership plan array from the currently checked federations every time there's an update.
     *
     * @return void
     */
    public function setLocalMembershipPlanArray()
    {
        $this->local_membership_plan_array = [];

        foreach ($this->federation_id_array as $index => $checked) {
            if ($checked) {
                $this->local_membership_plan_array[] = $this->federations[$index]->membership_plan_id;
            }
        }
    }

    public function resetLocalMembershipPlanArray()
    {
        $this->local_membership_plan_array = [];
    }

    public function render()
    {
        // select federations by country
        if (! empty($this->country_id)) {
            $this->getFederationsByCountry();
        }

        if (! empty($this->federation_id_array)) {
            $this->getLocalFederations();
        } else {
            $this->local_federations = null;
            $this->local_federation_id = null;
        }

        return view('livewire.public-individual-federation-choice');
    }

    public function getFederationsByCountry()
    {
        // clear previous choices
        if (! empty($this->country_id)) {
            $this->federations = Cache::remember('federations_by_country_'.$this->country_id, 0, function () {
                $results = DB::table('federation')
                    ->leftJoin('membership', 'federation.id', '=', 'membership.federation_id')
                    ->leftJoin('membership_membership_plan', 'membership.id', '=', 'membership_membership_plan.membership_id')
                    ->leftJoin('membership_plan', 'membership_membership_plan.membership_plan_id', '=', 'membership_plan.id')
                    ->leftJoin('committee', 'membership_plan.committee_id', '=', 'committee.id')
                    ->where('federation.country_id', $this->country_id)
                    ->whereNull('federation.parent_id')
                    ->where('membership.status_class', ActiveMembershipState::class)
                    ->where('membership_plan.committee_id', '<>', 0) // Exclude results with committee_id of 0
                    ->select([
                        'federation.id as federation_id',
                        'committee.name as committee_name',
                        'membership_plan.friendly_name as membership_plan_friendly_name',
                        'membership_plan.name as membership_plan_name',
                        'membership_plan.id as membership_plan_id',
                    ])
                    ->get();

                return collect($results)
                    ->map(function ($item, $index) {
                        // Check if the friendly name is not empty we want to skip the records where the friendly name is empty
                        if (! empty($item->membership_plan_friendly_name)) {
                            return (object) [
                                'index' => $index,
                                'id' => $item->federation_id,
                                'membership' => $item->membership_plan_friendly_name,
                                'membership_plan_id' => $item->membership_plan_id,
                            ];
                        }

                        return null;
                    })
                    ->filter() // Filters out the null values
                    ->values() // Resets the keys
                    ->all();
            });

        } else {
            $this->federations = null;
            $this->federation_id_array = null;
            $this->error_message = 'No organizations found for the country selected';
        }
    }

    public function getLocalFederations()
    {

        $flattened_federation_id_array = array_values(Arr::flatten($this->federation_id_array));
        $flattened_membership_plan_array = array_values(Arr::flatten($this->local_membership_plan_array));

        if (! empty($flattened_federation_id_array) && ! empty($flattened_membership_plan_array)) {
            $this->local_federations = Federation::whereIn('parent_id', $flattened_federation_id_array)
                ->whereHas('memberships.plans', function ($query) use ($flattened_membership_plan_array) {
                    $query->whereIn('membership_plan.id', $flattened_membership_plan_array);
                })
                ->pluck('name', 'id');
        } else {
            $this->local_federations = null;
        }
    }
}
