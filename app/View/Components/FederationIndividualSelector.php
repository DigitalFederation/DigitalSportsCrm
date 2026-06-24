<?php

namespace App\View\Components;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * IndividualSelector Component
 *
 * This component provides a reusable individual selector functionality that can be
 * attached to any input field. It opens a modal with a searchable table of individuals
 * from a specified federation, allowing users to easily select an individual and
 * populate the input field with their international code.
 *
 * Usage:
 * <div class="relative">
 *     <input type="text" id="individual_code" name="individual_code" class="form-input w-full">
 *     <x-individual-selector input-id="individual_code" :federation-id="$federationId" />
 * </div>
 *
 *
 * @property string $inputId The ID of the input field to be populated with the selected individual's international code
 * @property int $federationId The ID of the federation to filter individuals (defaults to the authenticated user's federation)
 *
 * @see resources/views/components/individual-selector.blade.php For the component's view
 * @see App\Livewire\IndividualSelectorModal For the Livewire component that handles the table functionality
 *
 * @example
 * // In a Blade view:
 * <x-individual-selector input-id="student_member_code" :federation-id="$currentFederationId" />
 */
class FederationIndividualSelector extends Component
{
    public $inputId;
    public $federationId;
    public $wireModel;

    public function __construct($inputId, $federationId = null, $wireModel = null)
    {
        $this->inputId = $inputId;
        $this->wireModel = $wireModel;

        $user = auth()->user();

        if ($federationId !== null) {
            $this->federationId = $federationId;
        } elseif ($user instanceof User && $user->isFederation()) {
            $this->federationId = $user->federations()->first()->id ?? null;
        } else {
            $this->federationId = null; // This will allow admin users to select from all individuals
        }
    }

    public function render(): View
    {
        return view('components.federation-individual-selector', ['input_element' => $this->inputId]);
    }
}
