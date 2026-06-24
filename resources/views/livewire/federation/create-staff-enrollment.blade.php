<div>
    <x-layout.banner_message />

    <!-- Explain how to use this page -->
    <x-information-box
        title="{{ __('Instructions') }}"
        body="{{ __('Choose from the list os Staff members you want to enroll for this event. If a member is not listed, check their role.') }}"></x-information-box>

    <!-- Table -->

    <div class="md:w-1/2 flex flex-col md:flex-row mb-2">
        <input type="text" wire:model="search" class="form-input rounded-tr-none rounded-br-none"
               placeholder="{{ __('Search by Name or Nº Filiado') }}">
        <button class="btn btn-primary rounded-none" wire:click="doSearch">Search</button>
        <button class="btn btn-info rounded-tl-none rounded-bl-none" wire:click="doClearSearch">Clear</button>
    </div>
    <x-dynamic-table :headers="['','Name', __('main.Member Code'),'']">
        @foreach($eligibleIndividuals as $individual)

            <tr>
                <td class="pl-5 py-2 w-12 text-center md:text-left">
                    <input type="checkbox"
                           class="rounded-md border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50"
                           wire:click="updateSelectedIndividuals('{{ json_encode(['id' => $individual->id, 'name' => $individual->full_name]) }}')"
                           id="checkbox_{{ $individual->id }}_{{ $this->page }}"
                           @if(in_array($individual->id, array_column($this->selectedIndividuals[$this->page] ?? [], 'id'))) checked @endif>
                    <label for="checkbox_{{ $individual->id }}_{{ $this->page }}"></label>
                </td>

                <td class="px-2 py-2 w-auto text-center md:text-left">
                    <a href="{{ route('admin.individual.show', $individual->id)}}"

                       class="text-slate-600 hover:text-slate-400 rounded-full">
                        <x-svg.box-arrow-up-right class="inline-block w-3 h-3 text-slate-400" />
                        <span>{{ $individual->full_name }}</span>
                    </a>
                </td>

                <td class="pl-4 py-2 w-auto text-center md:text-left">
                    {{ $individual->member_code }}
                </td>

                <td class="pr-5 py-2 w-auto text-right whitespace-wrap break-words">
                    @foreach($individual->professionalRoles()->pluck('name')->unique() as $role)
                        <span
                            class="inline-flex items-center justify-center px-2 py-1 text-xs leading-none text-white bg-blue-500 rounded-full my-2">
                            {{ $role }}
                        </span>
                    @endforeach
                </td>
            </tr>
        @endforeach
    </x-dynamic-table>

    <div class="mt-4">
        {{ $eligibleIndividuals->links() }}
    </div>

    <!-- Selected members section -->

    <div class="mt-4 card ">

        <div class="flex justify-between items-start">
            <div class="font-bold text-lg mb-4">
                Selected <span
                    class="text-slate-400 text-sm">({{ count($this->getFlattenedSelectedIndividuals()) }})</span>
            </div>


            <div class="flex items-center">
                <!-- Display Total Cost -->
                @if($totalCost > 0)
                    <p class="font-bold text-slate-600">Total: <span class="font-normal">{{ number_format($totalCost, 2, '.', '') }}€</span>
                    </p>
                @endif
            </div>
        </div>

        @if(!empty($this->selectedIndividuals))
            <div class="md:max-h-80 overflow-y-auto">
                <x-dynamic-table :headers="[]">
                    @foreach(array_merge(...$this->selectedIndividuals) as $selected)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-1 whitespace-nowrap w-px">{{ $selected['name'] }}</td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            </div>
        @endif


        <div class="justify-end flex mt-4  border-t pt-4 border-slate-400">
            @if(empty($this->showConfirmation))
                <button
                    wire:click="doShowConfirmation"
                    class="btn btn-info"
                    type="button" @if(empty($this->selectedIndividuals)) disabled @endif> Create Enrollment
                </button>
            @else
                <div class="flex items-center gap-x-2 gap-y-2">
                    <div class="w-auto block px-2">
                        @if($totalCost > 0)
                            Are <u>you sure</u> you want to enroll {{ count($this->getFlattenedSelectedIndividuals()) }}
                            members for a total of {{ number_format($totalCost, 2, '.', '') }}€ ?
                        @else
                            Are <u>you sure</u> you want to enroll {{ count($this->getFlattenedSelectedIndividuals()) }}
                            members?
                        @endif

                    </div>
                    <button wire:click="submitEnrollment" class="btn btn-primary" type="button">
                        Submit Enrollment
                    </button>
                </div>
            @endif
        </div>


    </div>


</div>
