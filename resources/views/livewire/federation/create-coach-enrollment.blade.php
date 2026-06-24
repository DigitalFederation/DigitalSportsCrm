<div>
    <!-- Table -->
    <div class="md:w-1/2 flex flex-col md:flex-row mb-2">
        <input type="text" wire:model="search" class="form-input rounded-tr-none rounded-br-none"
               placeholder="{{ __('Search by Name or Nº Filiado') }}">
        <button class="btn btn-primary rounded-none" wire:click="doSearch">Search</button>
        <button class="btn btn-info rounded-tl-none rounded-bl-none" wire:click="doClearSearch">Clear</button>
    </div>

    <x-dynamic-table :headers="['Selection','Name', __('main.Member Code'),'Roles']">
        @foreach($individuals as $individual)
            <tr>
                <td class="pl-5 py-2 w-12 text-center md:text-left">
                    <input type="checkbox"
                           class="rounded-md border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50"
                           wire:click="toggleMember('{{ $individual->id }}')"
                           @if(in_array($individual->id, $selectedMembers)) checked @endif>
                </td>
                <td class="px-2 py-2 w-auto text-center md:text-left">
                    <a href="{{ route('admin.individual.show', $individual->id)}}"
                       class="text-slate-600 hover:text-slate-400 rounded-full">
                        <x-svg.box-arrow-up-right class="inline-block w-3 h-3 text-slate-400" />
                        <span>{{ $individual->name }} {{ $individual->surname }}</span>
                    </a>
                </td>
                <td class="pr-5 py-2 w-auto text-center md:text-left">
                    {{ $individual->member_code }}
                </td>
                <td class="pr-5 py-2 w-auto text-right whitespace-wrap break-words">

                    @foreach($individual->professionalRoles()->where('role', 'COACH')->pluck('name') as $role)
                        <span
                            class="inline-flex items-center justify-center px-2 py-1 text-xs font-semibold leading-none text-white bg-blue-500 rounded-full my-2">
                            {{ $role }}
                        </span>
                    @endforeach
                </td>
            </tr>

        @endforeach
    </x-dynamic-table>

    <div class="mt-4">
        {{ $individuals->links() }}
    </div>

    <!-- Selected members section -->
    @if(!empty($this->selectedMembers))
        <div class="mt-4 card">
            <h3 class="font-bold text-lg mb-4">Selected Coaches <span class="text-slate-400 text-sm">({{ count($this->selectedMembers) }})</span>
            </h3>
            <ul class="md:max-h-80 overflow-y-auto">
                @foreach($this->selectedMembersDetails as $member)
                    <li class="border-b first:border-t py-1 border-slate-200">{{ $member['full_name'] }} </li>
                @endforeach
            </ul>

            <div class="justify-end flex mt-4  border-t pt-4 border-slate-400">
                @if(empty($this->showConfirmation))
                    <button
                        wire:click="doShowConfirmation"
                        class="btn btn-info"
                        type="button" @if(empty($this->selectedMembers)) disabled @endif> Submit Enrollment
                    </button>
                @else
                    <div class="flex items-center gap-x-2 gap-y-2">
                        <div class="w-auto block px-2">
                            Are <u>you sure</u> you want to
                            enroll {{ count($this->selectedMembers) }} members?
                        </div>
                        <button wire:click="saveCoaches" class="btn btn-primary" type="button">
                            Submit Enrollment
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

</div>
