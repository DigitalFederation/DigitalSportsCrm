<div>
    <div class="card mt-2">
        <h2 class="font-semibold text-slate-600 text-lg">{{ __('Manage Staff Roles') }}</h2>
        <p class=" text-slate-400 text-sm mb-4">Add or remove staff roles for this member</p>

        <!-- Select Input for Federations associated with the Individual -->
        <select
            wire:model="selectedFederationId"
            wire:change="loadRoles"
            class="form-select mb-4 w-full">
            <option value="">Select a Federation</option>
            @foreach ($federations as $federationId => $federationName)
                <option value="{{ $federationId }}">{{ $federationName }}</option>
            @endforeach
        </select>

        @if($selectedFederationId)
            <!-- Select Input for Available Roles -->
            <div class="flex items-start flex-row w-full">
                <select wire:model="selectedRole" class="form-select mb-4 w-3/4">
                    <option value="">Select a Role
                    </option>
                    @foreach ($allStaffRoles as $roleId => $roleName)
                        <option value="{{ $roleId }}">{{ $roleName }}</option>
                    @endforeach
                </select>
                <button wire:click="addRole" class="btn rounded-l-none btn-primary w-1/4">Add</button>
            </div>

            <!-- List of Added Roles -->
            <ul class="flex gap-2 flex-wrap">
                @foreach ($addedRoles as $roleId => $roleName)
                    <li class="flex items-center border border-slate-400 text-slate-500 rounded-md w-fit px-2 gap-2">
                        <div class="text-sm px-2 py-1">{{ $roleName }}</div>

                        <div wire:click="removeRole({{ $roleId }})" class="text-red-500 cursor-pointer">
                            <x-svg.trash class="w-5 h-5"></x-svg.trash>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif


    </div>
</div>
