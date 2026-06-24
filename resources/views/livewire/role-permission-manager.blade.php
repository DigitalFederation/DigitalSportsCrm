<div class="card w-full">
    <div class="mb-4">
        <label for="role" class="block text-sm font-medium text-gray-700">Select Role</label>
        <select id="role" wire:model.live="selectedRoleId"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option value="">Choose a role...</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
            @endforeach
        </select>
    </div>

    @if ($selectedRoleId)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($permissions as $permission)
                <label class="flex items-center">
                    <input type="checkbox" value="{{ $permission->id }}" wire:model="assignedPermissions"
                           class="form-checkbox">
                    <span class="ml-2 text-sm text-gray-600">{{ $permission->name }}</span>
                </label>
            @endforeach
        </div>
        <button wire:click="save"
                wire:loading.attr="disabled"
                class="mt-4 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <span wire:loading.remove wire:target="save">Save Permissions</span>
            <div wire:loading wire:target="save" class="flex flex-row items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0116 0H4z"></path>
                </svg>
                <span>Saving...</span>
            </div>
        </button>

    @endif

    @if (session()->has('message'))
        <div class="mt-3 text-sm text-green-600">
            {{ session('message') }}
        </div>
    @endif
</div>
