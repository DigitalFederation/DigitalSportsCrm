<div class="mt-4">

    <x-information-box
        title="Note"
        body="Staff can be added to a competition only if they are already registered in the system.">
    </x-information-box>

    <!-- Search Box -->
    <div class="mb-4">
        <input
            wire:model.live="searchCode"
            type="text"
            placeholder="{{ __('Write a Nº Filiado to search for staff...') }}"
            class="form-input w-full"
        />
    </div>

    @if($message)
        <div class="my-4" wire:poll.3s="resetMessage">
            <p class="text-slate-600 text-sm"> {{ $message }} </p>
        </div>
    @endif

    <!-- Search Results -->
    @if(strlen($searchCode) >= 3)
        <ul class="mb-4">
            @forelse($individuals as $individual)
                <li class="flex justify-between items-center border-b py-2">
                    <a href="{{ route('admin.individual.show', $individual->id) }}"
                       class="hover:text-blue-400 transition" target="_blank">{{ $individual->full_name }} |
                        <small>{{ $individual->member_code }}</small></a>
                    <button
                        type="button"
                        wire:click="addStaff('{{ $individual->id }}')"
                        class="btn btn-sm btn-info flex flex-row gap-x-2">
                        <x-svg.plus class="w-2 h-2" />
                        <span>add</span>
                    </button>
                </li>
            @empty
                <li>No results found.</li>
            @endforelse
        </ul>
    @endif

    <!-- Added Referees Table -->
    @if(!empty($staff) && $staff->count() > 0)
        <x-dynamic-table :headers="['Code', 'Name', 'Action']">
            @foreach($staff as $stf)
                <tr>
                    <td class="px-2 py-2 first:pl-5 last:pr-5 w-px">{{ $stf->individual->member_code }}</td>
                    <td class="px-2 py-2 first:pl-5 last:pr-5 w-px">{{ $stf->individual->full_name }}</td>
                    <td class="px-2 py-2 first:pl-5 last:pr-5 w-px items-center text-right">
                        <button type="button" wire:click="confirmStaffRemoval({{ $stf->id }})">
                            <x-svg.trash class="w-4 h-4 text-red-500" />
                        </button>
                    </td>
                </tr>
            @endforeach
        </x-dynamic-table>
    @endif


    <!-- Confirmation Modal -->
    @if($confirmingStaffRemoval)
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="bg-white p-4 rounded-lg shadow-lg border border-slate-200">
                <h3 class="font-bold text-lg">Warning</h3>
                <p>Are you sure you want to remove this staff member?</p>
                <div class="flex justify-end mt-4 gap-x-2">
                    <button wire:click="removeStaff({{ $staffIdToRemove }})"
                            class="btn btn-sm bg-red-600 text-white">Yes
                    </button>
                    <button wire:click="$set('confirmingStaffRemoval', false)"
                            class="btn btn-sm bg-gray-300 text-black">No
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
