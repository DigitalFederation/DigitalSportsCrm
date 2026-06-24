<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-end sm:items-center mb-4">

            <!-- Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2 items-center">
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open"
                            class="btn btn-secondary"
                            type="button">
                        {{ __('events.configurations') }}
                        <x-svg.chevron-down class="w-3 h-3 ml-1" />
                    </button>
                    <!-- Dropdown menu -->
                    <div x-cloak x-show="open"
                         class="absolute right-0 z-50 bg-white divide-y divide-gray-100 rounded-lg shadow-lg w-auto whitespace-nowrap border border-slate-200"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95">
                        <ul class="py-2 text-sm text-slate-700">
                            <li>
                                <a href="{{ route('admin.evt-events.discipline-templates.index') }}"
                                   class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50">
                                    <x-svg.boxes class="w-4 h-4" />
                                    {{ __('events.discipline_templates') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.evt-events.disciplines.index') }}"
                                   class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50">
                                    <x-svg.boxes class="w-4 h-4" />
                                    {{ __('events.disciplines') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.evt-events.attributes.index') }}"
                                   class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50">
                                    <x-svg.boxes class="w-4 h-4" />
                                    {{ __('events.attributes') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.evt-events.sport-age-groups.index') }}"
                                   class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50">
                                    <x-svg.people-group class="w-4 h-4" />
                                    {{ __('events.sport_age_groups') }}
                                </a>
                            </li>
                            <li>
                                <x-dynamic-modal
                                    :viewName="'anti-doping-pin-manager'"
                                    :params="[]"
                                    headerTitle="{{ __('events.antidoping_pin_title') }}"
                                    buttonLabel="{{ __('events.antidoping_pin') }}"
                                    buttonClass="px-4 py-2 w-full cursor-pointer items-center hover:bg-slate-50"
                                    :isLivewire="true"
                                    iconComponent="svg.key"
                                    animation="transition ease-in duration-200"
                                />
                            </li>
                            <li>
                                <a href="{{ route('admin.evt-events.events.master.index') }}"
                                   class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50">
                                    <x-svg.boxes class="w-4 h-4" />
                                    {{ __('events.master_list') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.evt-events.sport.index') }}"
                                   class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50">
                                    <x-svg.boxes class="w-4 h-4" />
                                    {{ __('events.sports_management') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.evt-events.event-images.index') }}"
                                   class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50">
                                    <x-heroicon-o-photo class="w-4 h-4" />
                                    {{ __('events.images') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <a class="btn btn-primary" href="{{ route('admin.evt-events.events.create', 'competition') }}">
                    {{ __('events.competition_event') }}
                </a>

                <a class="btn btn-primary" href="{{ route('admin.evt-events.events.create', 'organization') }}">
                    {{ __('events.organization_event') }}
                </a>
            </div>

        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            @livewire('admin.evt-events.events-table')
        </div>


    </div>
</x-layout>
