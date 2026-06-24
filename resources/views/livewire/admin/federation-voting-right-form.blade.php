<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
    <form wire:submit.prevent="save">
        <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">
            Edit Voting Rights for {{ $federation->name }} ({{ $year }})
        </h2>

        {{-- Standard Blade Validation Errors --}}
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded dark:bg-red-900/30 dark:border-red-600 dark:text-red-300">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-6">
            {{-- General Assembly & Committees --}}
            <fieldset class="border border-gray-300 dark:border-gray-600 p-4 rounded-md">
                <legend class="text-lg font-medium text-gray-900 dark:text-gray-100 px-2">Assembly & Committees</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-4">
                    <div>
                        <label for="general_assembly_status" class="input-label">General Assembly</label>
                        <select id="general_assembly_status" wire:model="general_assembly_status" class="input-select">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="technical_committee_status" class="input-label">Technical Committee</label>
                        <select id="technical_committee_status" wire:model="technical_committee_status" class="input-select">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="scientific_committee_status" class="input-label">Scientific Committee</label>
                        <select id="scientific_committee_status" wire:model="scientific_committee_status" class="input-select">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="sport_committee_status" class="input-label">Sport Committee</label>
                        <select id="sport_committee_status" wire:model="sport_committee_status" class="input-select">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </fieldset>

            {{-- Sport Commissions --}}
            <fieldset class="border border-gray-300 dark:border-gray-600 p-4 rounded-md">
                <legend class="text-lg font-medium text-gray-900 dark:text-gray-100 px-2">Sport Commissions</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 pt-4">
                    <div>
                        <label for="finswimming_commission_status" class="input-label">Finswimming</label>
                        <select id="finswimming_commission_status" wire:model="finswimming_commission_status" class="input-select">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="freediving_commission_status" class="input-label">Freediving</label>
                        <select id="freediving_commission_status" wire:model="freediving_commission_status" class="input-select">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="aquathlon_commission_status" class="input-label">Aquathlon</label>
                        <select id="aquathlon_commission_status" wire:model="aquathlon_commission_status" class="input-select">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="underwater_hockey_commission_status" class="input-label">UW Hockey</label>
                        <select id="underwater_hockey_commission_status" wire:model="underwater_hockey_commission_status" class="input-select">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="underwater_rugby_commission_status" class="input-label">UW Rugby</label>
                        <select id="underwater_rugby_commission_status" wire:model="underwater_rugby_commission_status" class="input-select">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="target_shooting_commission_status" class="input-label">Target Shooting</label>
                        <select id="target_shooting_commission_status" wire:model="target_shooting_commission_status" class="input-select">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="sport_diving_commission_status" class="input-label">Sport Diving</label>
                        <select id="sport_diving_commission_status" wire:model="sport_diving_commission_status" class="input-select">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="spearfishing_commission_status" class="input-label">Spearfishing</label>
                        <select id="spearfishing_commission_status" wire:model="spearfishing_commission_status" class="input-select">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="orienteering_commission_status" class="input-label">Orienteering</label>
                        <select id="orienteering_commission_status" wire:model="orienteering_commission_status" class="input-select">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="visual_commission_status" class="input-label">Visual</label>
                        <select id="visual_commission_status" wire:model="visual_commission_status" class="input-select">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </fieldset>
        </div>

        {{-- Action Buttons --}}
        <div class="flex justify-end space-x-4 mt-6">
            <button type="button" wire:click="cancel"
                    class="btn-secondary">
                Cancel
            </button>
            <button type="submit"
                    class="btn-primary">
                Save Changes
            </button>
        </div>
    </form>
</div>
