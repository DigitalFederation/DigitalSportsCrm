<div class="space-y-8">
    <!-- Hero Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <div class="max-w-4xl">
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $event->name }} - Pre-Registration
            </h1>
            <p class="mt-2 text-gray-600">
                Register participants for this event by selecting their roles and adding them to your registration list.
                Complete all selections before proceeding to payment.
            </p>

            <!-- Registration Stats -->
            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <dt class="text-sm font-medium text-gray-500">Total Selected</dt>
                    <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                        <div class="flex items-baseline text-2xl font-semibold text-primary-600">
                            {{ array_sum(array_map('count', $selectedParticipants)) }}
                            <span class="ml-2 text-sm font-medium text-gray-500">participants</span>
                        </div>
                    </dd>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <dt class="text-sm font-medium text-gray-500">Total Cost</dt>
                    <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                        <div class="flex items-baseline text-2xl font-semibold text-primary-600">
                            €{{ number_format($totalCost, 2) }}
                        </div>
                    </dd>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <dt class="text-sm font-medium text-gray-500">Registration Status</dt>
                    <dd class="mt-1">
                        <span
                            class="px-2 py-1 text-xs font-medium rounded-full {{ $totalCost > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $totalCost > 0 ? 'Ready to Submit' : 'Select Participants' }}
                        </span>
                    </dd>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Tabs -->
    <div x-data="{ tab: 'register' }" class="card">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button
                    @click="tab = 'register'"
                    :class="{ 'border-primary-500 text-primary-600': tab === 'register' }"
                    class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm"
                >
                    <x-heroicon-m-user-plus class="h-5 w-5 mr-2" />
                    Registration
                </button>

                <button
                    @click="tab = 'pending'"
                    :class="{ 'border-primary-500 text-primary-600': tab === 'pending' }"
                    class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm"
                >
                    <x-heroicon-m-clock class="h-5 w-5 mr-2" />
                    Pending Payments
                    @if($pendingEnrollments->count())
                        <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ $pendingEnrollments->count() }}
                        </span>
                    @endif
                </button>
            </nav>
        </div>

        <!-- Registration Tab -->
        <div x-show="tab === 'register'" class="mt-6">
            <div class="grid grid-cols-12 gap-6">
                <!-- Left Column: Role Selection & Participant Table -->
                <div class="col-span-8">
                    <!-- Role Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Role</label>
                        <div class="flex space-x-4">
                            @foreach($availableRoles as $role => $isAvailable)
                                @if($isAvailable)
                                    <button
                                        wire:key="role-button-{{ $role }}"
                                        wire:click="$set('activeRole', '{{ $role }}')"
                                        type="button"
                                        class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium
                                        {{ $activeRole === $role
                                            ? 'bg-primary-500 text-white'
                                            : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}"
                                    >
                                        {{ ucfirst($role) }}s
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Participant Selection Table -->
                    <div class="bg-white rounded-lg shadow">
                        {{ $this->table }}
                    </div>
                </div>

                <!-- Right Column: Selected Participants -->
                <div class="col-span-4">
                    <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                        <h3 class="text-lg font-medium text-gray-600 mb-4 border-slate-300 border-b pb-2">Selected
                            Participants</h3>

                        <div class="space-y-6">
                            @foreach($selectedParticipants as $role => $participants)
                                @if(count($participants) > 0)
                                    <div class="border-b border-gray-200 pb-4">
                                        <h4 class="text-sm font-medium text-gray-900 flex justify-between items-center">
                                            {{ ucfirst($role) }}s
                                            <span class="text-gray-500 text-xs">
                                                €{{ number_format($roleCosts[$role] ?? 0, 2) }}
                                            </span>
                                        </h4>

                                        <ul class="mt-3 space-y-2">
                                            @foreach($participants as $participant)
                                                <li class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-600">{{ $participant['full_name'] ?? $participant['name'] }}</span>
                                                    <button
                                                        wire:click="removeParticipant('{{ $role }}', '{{ $participant['id'] }}')"
                                                        class="text-red-500 hover:text-red-700"
                                                    >
                                                        <x-heroicon-m-x-mark class="h-4 w-4" />
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        {{-- Per-Discipline Pricing: Discipline Selection Section --}}
                        @if ($requiresDisciplineSelection && count($selectedParticipants['athlete'] ?? []) > 0)
                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                                    <div class="flex items-start">
                                        <x-heroicon-m-exclamation-triangle class="w-5 h-5 text-amber-600 mr-2 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <h4 class="font-medium text-amber-800 text-sm">{{ __('events.discipline_selection_required') }}</h4>
                                            <p class="text-xs text-amber-700 mt-1">{{ __('events.per_discipline_pricing_notice') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="text-sm font-medium text-gray-900 mb-3">{{ __('events.assign_disciplines') }}</h4>

                                <div class="space-y-4">
                                    @foreach ($selectedParticipants['athlete'] as $participant)
                                        <div class="border border-gray-200 rounded-lg p-3" wire:key="discipline-selection-{{ $participant['id'] }}">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-medium text-gray-700 text-sm">{{ $participant['full_name'] ?? $participant['name'] }}</span>
                                                @php
                                                    $selectedCount = count($disciplinesByAthlete[$participant['id']] ?? []);
                                                @endphp
                                                @if ($selectedCount > 0)
                                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded-full">
                                                        {{ $selectedCount }} {{ __('selected') }}
                                                    </span>
                                                @else
                                                    <span class="text-xs bg-red-100 text-red-800 px-2 py-0.5 rounded-full">
                                                        {{ __('None selected') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                @if ($availableDisciplines && count($availableDisciplines) > 0)
                                                    @foreach ($availableDisciplines as $discipline)
                                                        @php
                                                            $isSelected = in_array($discipline->id, $disciplinesByAthlete[$participant['id']] ?? []);
                                                            $price = $disciplinePricing[$discipline->id]['price'] ?? 0;
                                                        @endphp
                                                        <button
                                                            type="button"
                                                            wire:click="toggleDisciplineForAthlete('{{ $participant['id'] }}', {{ $discipline->id }})"
                                                            @class([
                                                                'px-2 py-1 rounded-full text-xs border transition-colors',
                                                                'bg-primary-100 border-primary-500 text-primary-700' => $isSelected,
                                                                'bg-gray-100 border-gray-300 text-gray-700 hover:bg-gray-200' => !$isSelected,
                                                            ])
                                                        >
                                                            {{ $discipline->name }}
                                                            <span class="font-medium">€{{ number_format($price, 2) }}</span>
                                                        </button>
                                                    @endforeach
                                                @else
                                                    <span class="text-xs text-gray-500">{{ __('No disciplines available') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Total and Submit -->
                        <div class="mt-6 space-y-4">
                            <div class="flex justify-between items-center text-lg font-semibold">
                                <span>Total</span>
                                <span>€{{ number_format($totalCost, 2) }}</span>
                            </div>

                            <button
                                wire:click="submitRegistration"
                                @class([
                                    'w-full px-4 py-2 rounded-md text-sm font-medium',
                                    'bg-primary-500 text-white hover:bg-primary-600' => $totalCost > 0,
                                    'bg-gray-100 text-gray-400 cursor-not-allowed' => $totalCost === 0,
                                ])
                                @disabled($totalCost === 0)
                            >
                                Submit Registration
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Payments Tab -->
        <div x-show="tab === 'pending'" class="mt-6">

                <ul role="list" class="divide-y divide-gray-200">
                    @forelse($pendingEnrollments as $enrollment)
                        <div class="bg-white rounded-lg shadow p-4 my-4">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold">
                                        Enrollment #{{ $enrollment['document_number'] }}
                                    </h3>
                                    <p class="text-gray-600">
                                        Total: {{ number_format($enrollment['total_price'], 2) }} €
                                    </p>
                                </div>
                                <a
                                    href="{{ route('federation.document.show', $enrollment['document_id']) }}"
                                    class="btn text-white bg-primary-600 hover:bg-primary-700">
                                    Pay Now
                                </a>
                            </div>

                            <div class="divide-y">
                                @foreach($enrollment['participants'] as $participant)
                                    <div class="py-2 flex justify-between items-center">
                                        <div>
                                            <span class="font-medium">{{ $participant['name'] }}</span>
                                            <span class="text-gray-500 text-sm ml-2">({{ $participant['role'] }})</span>
                                        </div>
                                        <span class="text-gray-600">
                                            {{ number_format($participant['price'], 2) }} €
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            No pending enrollments found.
                        </div>
                    @endforelse
                </ul>

        </div>
    </div>
</div>
