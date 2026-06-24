<div>
    @if($selectedFederationId)
        <div class="p-4 border border-blue-400 rounded-md mb-4">
            <p class="text-slate-600">Current Association</p>

            @foreach($entity->entityFederations as $entityFederation)
                <p class="font-bold text-sm mb-4">{{ $entityFederation->first()->federation->name }}</p>

                <!-- Modal Dialog -->
                <x-filament::modal>
                    <x-slot name="trigger">
                        <x-filament::button
                            class="btn-danger"
                            size="sm"
                            tooltip="Remove the association from the National Federation">
                            Remove
                        </x-filament::button>
                    </x-slot>


                    <x-slot name="heading">
                        Confirm Removal
                    </x-slot>

                    <x-slot name="description">
                        <p>Are you sure you want to remove this association?</p>
                    </x-slot>

                    <x-slot name="footer">
                        <button type="button"
                                wire:click="removeAssociation"
                                class="btn-sm btn-danger">Yes, Remove
                        </button>
                    </x-slot>
                </x-filament::modal>
            @endforeach


        </div>
    @else
        <x-information-box title="Information"
                           :body="__('Confirm your association by choosing from the list below. The federation must accept this request afterwards.')" />
        <div class="mt-5">
            <label for="federation_id"
                   class="block text-sm font-medium mb-1">{{ __('Association to Federation') }}</label>
            <select wire:model="selectedFederationId" id="federation_id" class="form-select w-full">
                <option value="">-- Select Option --</option>
                @foreach($federations as $federation)
                    <option value="{{ $federation->id }}">{{ $federation->name }}</option>
                @endforeach
            </select>
        </div>

        <button wire:click="updateAssociation" type="button" class="mt-4 btn btn-primary">
            Associate with Federation
        </button>
    @endif


</div>
