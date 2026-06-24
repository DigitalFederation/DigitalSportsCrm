<div>
    {{-- Filters Card --}}
    <div class="card mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            {{-- Discipline Filter --}}
            <div>
                <label for="discipline_filter" class="block text-sm font-medium text-gray-700">{{ __('Filter by Discipline') }}</label>
                <select id="discipline_filter" wire:model.live="selectedDisciplineId"
                    class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                    <option value="">{{ __('All Disciplines') }}</option>
                    @foreach ($disciplines as $discipline)
                        <option value="{{ $discipline->id }}">{{ $discipline->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Organization/Team Filter --}}
            <div>
                <label for="organization_filter" class="block text-sm font-medium text-gray-700">{{ __('Search by National Federation or Club') }}</label>
                <input type="text" id="organization_filter" wire:model.live.debounce.500ms="searchOrganization"
                    placeholder="{{ __('Enter name...') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            {{-- Clear Filters Button & Count --}}
            <div class="flex justify-between items-center md:justify-end">
                <button wire:click="clearFilters" class="btn btn-secondary">
                    {{ __('Clear Filters') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Enrollments List --}}
    <div class="space-y-6">
        @forelse ($groupedEnrollments as $disciplineName => $teams)
            @if (!$teams->isEmpty())
                <div class="card" x-data="{ open: false }">
                    {{-- Discipline Header --}}
                    <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                        <h2 class="text-xl font-semibold text-gray-900">
                            {{ $disciplineName }}
                        </h2>
                        <button class="text-gray-400 hover:text-gray-500">
                            <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': open }"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

                    {{-- Organizations within Discipline --}}
                    <div x-show="open" x-collapse>
                        @foreach ($teams as $organization => $athletes)
                            <div class="mt-4">
                                {{-- Organization Header --}}
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">{{ $organization }}</h3>
                                    <span class="text-sm text-gray-500">{{ $athletes->count() }} {{ __('Athlete(s)') }}</span>
                                </div>

                                {{-- Athletes Table Wrapper for Horizontal Scrolling --}}
                                <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-300">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col"
                                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                                    {{ __('Athlete') }}</th>
                                                <th scope="col"
                                                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                    {{ __('Country') }}</th>
                                                <th scope="col"
                                                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                    {{ __('Gender') }}</th>
                                                <th scope="col"
                                                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                    {{ __('DoB') }}</th>
                                                {{-- Dynamic Attribute Headers --}}
                                                @if ($athletes->first() && $athletes->first()->attributes)
                                                    @php
                                                        // Determine unique attributes across all athletes in this specific table instance
                                                        $uniqueTableAttributes = $athletes->flatMap->attributes->pluck('attribute')->unique('id');
                                                    @endphp
                                                    @foreach ($uniqueTableAttributes as $attribute)
                                                        <th scope="col"
                                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                            {{ $attribute->name }}
                                                        </th>
                                                    @endforeach
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            @foreach ($athletes as $enrollment)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 sm:pl-6">
                                                        <div class="flex items-center">
                                                            @if($enrollment->individual)
                                                                <x-secure-profile-image :individual="$enrollment->individual" size="thumb" class="h-12 w-12 rounded-full object-cover" />
                                                            @else
                                                                <img src="{{ asset('images/placeholder-avatar.png') }}" alt="No photo" class="h-12 w-12 rounded-full object-cover" />
                                                            @endif
                                                            <div class="ml-4">
                                                                <div class="flex items-center gap-x-2">
                                                                    <div class="font-medium text-gray-900">
                                                                        <span class="uppercase">{{ $enrollment->individual?->surname }}</span>,
                                                                        {{ $enrollment->individual?->name }}
                                                                    </div>
                                                                    @if ($enrollment->individual?->member_code)
                                                                    <div class="text-gray-500 text-xs">
                                                                        [{{ $enrollment->individual?->member_code }}]
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                                        <div class="flex items-center">
                                                            @if ($enrollment->individual?->country?->iso)
                                                            <img src="{{ asset('img/flags/' . strtolower($enrollment->individual->country->iso) . '.svg') }}"
                                                                alt="flag" class="w-4 h-4 mr-1" />
                                                            @endif
                                                            {{ $enrollment->individual?->country?->name }}
                                                        </div>
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                                        {{ $enrollment->individual?->gender ? __(ucfirst($enrollment->individual->gender)) : '' }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                                        {{-- Check if birthdate is a Carbon instance before formatting --}}
                                                        {{ $enrollment->individual?->birthdate instanceof \Carbon\Carbon ? $enrollment->individual->birthdate->format('d/m/Y') : ($enrollment->individual?->birthdate ?? '') }}
                                                    </td>
                                                     {{-- Dynamic Attribute Values --}}
                                                    @if ($athletes->first() && $athletes->first()->attributes)
                                                        @php
                                                            $enrollmentAttributes = $enrollment->attributes->keyBy('attribute_id');
                                                        @endphp
                                                        @foreach ($uniqueTableAttributes as $attribute)
                                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                                                {{ $enrollmentAttributes->get($attribute->id)?->value ?? '--' }}
                                                            </td>
                                                        @endforeach
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @empty
            <div class="card text-center py-12">
                <p class="text-gray-500">{{ __('No athletes found matching your criteria.') }}</p>
            </div>
        @endforelse
    </div>
</div>
