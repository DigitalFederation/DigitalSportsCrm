@php
    $namespace = $this->model instanceof \Domain\Federations\Models\Federation ? 'federation' : 'entity';
    $wizardSteps = [
        ['number' => 1, 'title' => __('events.step1_title'), 'description' => __('events.registration')],
        ['number' => 2, 'title' => __('events.step2_title'), 'description' => __('events.enrollment')],
        ['number' => 3, 'title' => __('events.step3_title'), 'description' => __('events.payment')],
    ];
@endphp

<div class="space-y-8">

    {{-- Wizard Step Indicator --}}
    <x-evt-events.wizard-step-indicator
        :currentStep="1"
        :steps="$wizardSteps"
        :event="$event"
        :model="$this->model"
    />

    {{-- Back Button Header --}}
    <div class="mb-6">
        <div class="flex md:flex-row flex-col items-center justify-between">
            <div class="flex items-center space-x-2">
                <a href="{{ route($namespace . '.evt-events.events.show', $event) }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <x-heroicon-m-arrow-left class="w-5 h-5 mr-2 -ml-1" />
                    {{ __('events.back_to_event', ['event' => $event->name]) }}
                </a>
            </div>
            <div class="flex items-center text-sm text-gray-500 mt-2 md:mt-0">
                <x-heroicon-m-calendar class="w-5 h-5 mr-1" />
                {{ $event->start_date->format('M d, Y') }} - {{ $event->end_date->format('M d, Y') }}
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <div class="max-w-full">
            <h1 class="text-2xl font-bold text-gray-900">
                {{ __('events.registration') }} :: {{ $event->name }}
            </h1>
            <p class="mt-2 text-gray-600">
                {{ __('events.registration_instructions') }}
            </p>

            <!-- Registration Stats -->
            <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4 w-full">

                <div class="bg-gray-50 p-4 rounded-lg">
                    <dt class="text-sm font-medium text-gray-500"> {{ __('events.total_selected') }}</dt>
                    <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                        <div class="flex items-baseline text-2xl font-semibold text-primary-600">
                            {{ array_sum(array_map('count', $selectedParticipants)) }}
                            <span class="ml-2 text-sm font-medium text-gray-500"> {{ __('events.participants') }}</span>
                        </div>
                    </dd>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <dt class="text-sm font-medium text-gray-500"> {{ __('events.total_selected') }}</dt>
                    <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                        <div class="flex items-baseline text-2xl font-semibold text-primary-600">
                            {{ count($selectedParticipants['athlete'] ?? []) }} {{ __('events.athletes') }}
                        </div>
                    </dd>
                </div>

            </div>

            <!-- Available Credits Section -->
            @php
                $hasUsableCredits = false;
                foreach ($availableCredits as $roleType => $credit) {
                    if (isset($credit['available_slots']) && $credit['available_slots'] > 0) {
                        $hasUsableCredits = true;
                        break;
                    }
                }
            @endphp
            @if ($hasUsableCredits)
                <div class="mt-4 overflow-hidden">
                    <div class="py-2 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ __('events.available_replacement_credits') }}
                        </h3>
                    </div>
                    <div class="py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach ($availableCredits as $roleType => $credit)
                                @if (isset($credit['available_slots']) && $credit['available_slots'] > 0)
                                    <div class="p-3 bg-indigo-50 dark:bg-indigo-900 rounded-md">
                                        <div class="flex flex-col">
                                            <div class="font-medium text-indigo-700 dark:text-indigo-200">
                                                @if ($roleType === 'official')
                                                    {{ __('Team Officials') }}
                                                @else
                                                    {{ ucfirst($roleType) }}
                                                @endif
                                            </div>
                                            <div class="text-lg font-bold text-indigo-800 dark:text-indigo-100">
                                                {{ $credit['available_slots'] }} slot(s)
                                            </div>
                                            @if (isset($credit['expires_at']) && $credit['expires_at'])
                                                <div class="text-xs text-indigo-600 dark:text-indigo-300 mt-1">
                                                    Expires:
                                                    {{ \Carbon\Carbon::parse($credit['expires_at'])->format('M d, Y') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Tabs -->
    <div x-data="{ tab: 'register' }" class="card">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="tab = 'register'" :class="{ 'border-primary-500 text-primary-600': tab === 'register' }"
                    class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                    <x-heroicon-m-user-plus class="h-5 w-5 mr-2" />
                    {{ __('Registration') }}
                </button>

                <button @click="tab = 'pending'" :class="{ 'border-primary-500 text-primary-600': tab === 'pending' }"
                    class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                    <x-heroicon-m-clock class="h-5 w-5 mr-2" />
                    {{ __('Pending Payments') }}
                    @if ($pendingEnrollments->count())
                        <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ $pendingEnrollments->count() }}
                        </span>
                    @endif
                </button>

                <!-- Add new tab for registered participants -->
                @if (!empty($registeredParticipants))
                    <button @click="tab = 'registered'"
                        :class="{ 'border-primary-500 text-primary-600': tab === 'registered' }"
                        class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                        <x-heroicon-m-user-group class="h-5 w-5 mr-2" />
                        {{ __('Registered Participants') }}
                        @if (!empty($registeredParticipants['participants']))
                            <span
                                class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $registeredParticipants['count'] }}
                            </span>
                        @endif
                    </button>
                @endif
            </nav>
        </div>

        <!-- Registration Tab -->
        <div x-show="tab === 'register'" class="mt-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left Column: Role Selection & Participant Table -->
                <div class="lg:col-span-8 order-2 lg:order-1">
                    <!-- Role Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Role</label>
                        <div class="flex flex-wrap space-x-4 space-y-2 sm:space-y-0">
                            @foreach ($availableRoles as $role => $isAvailable)
                                @if ($isAvailable)
                                    <button wire:key="role-button-{{ $role }}"
                                        wire:click="$set('activeRole', '{{ $role }}')" type="button"
                                        class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 rounded-md text-sm font-medium
                                        {{ $activeRole === $role
                                            ? 'bg-primary-500 text-white'
                                            : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
                                        @php
                                            $roleLabel = match ($role) {
                                                'coach' => 'Coaches',
                                                'official' => 'Team Officials',
                                                default => ucfirst($role) . 's',
                                            };
                                        @endphp
                                        {{ $roleLabel }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div>
                        @if ($activeRole === 'coach' && $event->competition?->requiredCoachCertifications()->exists())
                            <div class="rounded-md bg-blue-50 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <x-heroicon-s-information-circle class="h-5 w-5 text-blue-400" />
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800">
                                            Required Certifications
                                        </h3>
                                        <div class="mt-2 text-sm text-blue-700">
                                            <p>
                                                Coaches must have the following certifications to participate:
                                                {{ $event->competition->requiredCoachCertifications()->pluck('name')->implode(', ') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <!-- Participant Selection Table -->
                    <div class="bg-white rounded-lg shadow">
                        {{ $this->table }}
                    </div>
                </div>

                <!-- Right Column: Selected Participants -->
                <div class="lg:col-span-4 order-1 lg:order-2">
                    <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                        <h3 class="text-lg font-medium text-gray-600 mb-4 border-slate-300 border-b pb-2">
                            {{ __('Selected Participants') }}
                        </h3>

                        <div class="space-y-6">
                            @foreach ($selectedParticipants as $role => $participants)
                                @if (count($participants) > 0)
                                    <div class="border-b border-gray-200 pb-4">
                                        <h4 class="text-sm font-medium text-gray-900 flex justify-between items-center">
                                            {{ ucfirst($role) }}s
                                            <span class="text-gray-500 text-xs">
                                                {{ money($roleCosts[$role] ?? 0) }}
                                            </span>
                                        </h4>

                                        <ul class="mt-3 space-y-2">
                                            @foreach ($participants as $participant)
                                                <li class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-600">{{ $participant['name'] }}</span>
                                                    <button
                                                        wire:click="removeParticipant('{{ $role }}', '{{ $participant['id'] }}')"
                                                        class="text-red-500 hover:text-red-700">
                                                        <x-heroicon-m-x-mark class="h-4 w-4" />
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-6 space-y-4">
                            @php
                                $hasParticipants = array_sum(array_map('count', $selectedParticipants)) > 0;
                            @endphp

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <div class="flex items-start text-blue-700 text-sm">
                                    <x-heroicon-m-information-circle class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" />
                                    <span>{{ __('events.step1_info') }}</span>
                                </div>
                            </div>

                            <button wire:click="submitRegistration"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-wait"
                                @class([
                                    'w-full px-4 py-2 rounded-md text-lg font-medium btn flex items-center justify-center',
                                    'btn-primary text-white hover:bg-primary-600' => $hasParticipants,
                                    'bg-gray-300 text-gray-500 cursor-not-allowed' => !$hasParticipants,
                                ])
                                @disabled(!$hasParticipants)>
                                <span wire:loading.remove wire:target="submitRegistration">
                                    {{ __('events.continue_to_step2') }}
                                </span>
                                <span wire:loading wire:target="submitRegistration" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('Processing...') }}
                                </span>
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
                                    Total: {{ money($enrollment['total_price']) }}
                                </p>
                            </div>
                            <a target="_blank"
                                href="{{ $model instanceof \Domain\Entities\Models\Entity ? route('entity.document.show', $enrollment['document_id']) : route('federation.document.show', $enrollment['document_id']) }}"
                                class="btn text-white bg-primary-600 hover:bg-primary-700">
                                {{ __('View Document') }}
                            </a>
                        </div>

                        <div class="divide-y">
                            @foreach ($enrollment['participants'] as $participant)
                                <div class="py-2 flex justify-between items-center">
                                    <div>
                                        <span class="font-medium">{{ $participant['name'] }}</span>
                                        <span class="text-gray-500 text-sm ml-2">({{ $participant['role'] }})</span>
                                    </div>
                                    <span class="text-gray-600">
                                        {{ money($participant['price']) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        {{ __('No pending enrollments found.') }}
                    </div>
                @endforelse
            </ul>
        </div>

        <!-- Registered Participants Tab -->
        <div x-show="tab === 'registered'" class="mt-6" x-cloak>
            @if (isset($error) && $error)
                <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-400 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-heroicon-s-exclamation-circle class="h-5 w-5 text-red-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">{{ __('Error Loading Participants') }}</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>{{ $error }}</p>
                                <p class="mt-1">
                                    {{ __('Please refresh the page or contact support if the issue persists.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="py-2">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ __('Currently Registered Participants') }}
                </h3>

                <!-- Rules Explanation Panel -->
                <div class="mb-6 bg-blue-50 rounded-lg p-4 border-l-4 border-blue-400">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-heroicon-s-information-circle class="h-5 w-5 text-blue-500" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-blue-800">{{ __('Participant Removal Rules') }}</h3>
                            <div class="mt-2 text-sm text-blue-700 space-y-1">
                                <p>{{ __('You can remove participants based on the following rules:') }}</p>
                                <ul class="list-disc pl-5 space-y-1 mt-1">
                                    <li>{{ __('⁠Coaches, referees, and team officials can be removed ONLY if they dont have attributes assigned') }}
                                    </li>
                                    <li>{{ __('Athletes can be removed ONLY if they don\'t have a discipline assigned') }}
                                    </li>
                                </ul>
                                <p class="mt-2">
                                    {{ __('If you need to make changes to an athlete with an assigned discipline, please contact support.') }}
                                </p>
                                <p class="mt-2 text-blue-800 font-medium">
                                    {{ __('For coaches and team officials, you can use the "Reset Attributes" action to clear all attributes while keeping them registered.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Controls -->
                <div class="mb-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div x-data="{
                            filterRole: 'all',
                            filterRemovable: 'all',
                            applyFilters() {
                                const rows = document.querySelectorAll('#registered-participants-table tbody tr');
                                rows.forEach(row => {
                                    const cells = row.querySelectorAll('td');
                                    if (cells.length < 3) return;

                                    // Get role from the second cell
                                    const role = cells[1].textContent.trim().toLowerCase();

                                    // Check if row has a lock icon (cannot be deleted)
                                    const hasLockIcon = row.querySelector('.text-gray-400.cursor-not-allowed') !== null;
                                    const canDelete = !hasLockIcon;

                                    let showByRole = this.filterRole === 'all' || this.filterRole === role;
                                    let showByRemovable = this.filterRemovable === 'all' ||
                                        (this.filterRemovable === 'removable' && canDelete) ||
                                        (this.filterRemovable === 'locked' && !canDelete);

                                    row.style.display = (showByRole && showByRemovable) ? '' : 'none';
                                });
                            }
                        }" x-init="applyFilters" @change="applyFilters"
                            class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1">{{ __('Filter by Role') }}</label>
                                <select x-model="filterRole"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                                    <option value="all">{{ __('All Roles') }}</option>
                                    <option value="athlete">{{ __('events.athletes') }}</option>
                                    <option value="coach">{{ __('events.coaches') }}</option>
                                    <option value="referee">{{ __('events.referees') }}</option>
                                    <option value="official">{{ __('Team Officials') }}</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1">{{ __('Filter by Removal Status') }}</label>
                                <select x-model="filterRemovable"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                                    <option value="all">{{ __('Show All') }}</option>
                                    <option value="removable">{{ __('Can Be Removed') }}</option>
                                    <option value="locked">{{ __('Cannot Be Removed') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                @if (empty($registeredParticipants['participants']))
                    <div class="text-center py-8">
                        <x-heroicon-o-user-group class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            {{ __('No participants registered yet') }}
                        </h3>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table id="registered-participants-table" class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Name') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Role') }}
                                    </th>

                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($registeredParticipants['participants'] as $participant)
                                    <tr
                                        class="{{ isset($participant['can_delete']) && !$participant['can_delete'] ? 'bg-red-50' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $participant['name'] }}
                                            @if (isset($participant['can_delete']) && !$participant['can_delete'])
                                                <span class="inline-flex items-center ml-2">
                                                    <x-heroicon-s-lock-closed class="h-4 w-4 text-red-500" />
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $participant['role'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full
                                                @if ($participant['status'] === 'paid') bg-green-100 text-green-800
                                                @elseif($participant['status'] === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($participant['status'] === 'pending_payment') bg-yellow-100 text-yellow-800
                                                @elseif($participant['status'] === 'pending_active') bg-yellow-100 text-yellow-800
                                                @elseif(
                                                    $participant['status'] === 'discipline_assigned' &&
                                                        isset($participant['discipline_id']) &&
                                                        $participant['discipline_id'] !== null) bg-blue-100 text-blue-800
                                                @elseif($participant['status'] === 'completed') bg-purple-100 text-purple-800
                                                @elseif($participant['status'] === 'active') bg-blue-100 text-blue-800
                                                @elseif($participant['status'] === 'canceled') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                @if ($participant['role'] === 'athlete')
                                                    @if (
                                                        $participant['status'] === 'discipline_assigned' &&
                                                            (empty($participant['discipline_id']) || $participant['discipline_id'] === null))
                                                        {{ __('Registered') }}
                                                    @elseif($participant['status'] === 'paid')
                                                        {{ __('Registered') }}
                                                    @else
                                                        {{ \App\Enums\EvtAthleteEnrollmentStatusEnum::toString($participant['status']) }}
                                                    @endif
                                                @elseif($participant['status'] === 'active')
                                                    {{ __('Active') }}
                                                @elseif($participant['status'] === 'pending')
                                                    {{ __('Pending') }}
                                                @elseif($participant['status'] === 'pending_payment')
                                                    {{ __('Pending Payment') }}
                                                @elseif($participant['status'] === 'pending_active')
                                                    {{ __('Pending Payment') }}
                                                @elseif($participant['status'] === 'canceled')
                                                    {{ __('Canceled') }}
                                                @else
                                                    {{ __(ucfirst(str_replace('_', ' ', $participant['status']))) }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if (isset($participant['can_delete']) && !$participant['can_delete'])
                                                <button class="text-gray-400 cursor-not-allowed"
                                                    x-tooltip="{{ __('Cannot remove athletes with assigned disciplines') }}"
                                                    disabled>
                                                    <x-heroicon-o-lock-closed class="w-5 h-5" />
                                                </button>
                                            @else
                                                <button
                                                    wire:click="confirmRemoval('{{ $participant['role'] }}', '{{ $participant['id'] }}')"
                                                    class="text-red-600 hover:text-red-900"
                                                    x-tooltip.raw="{{ __('Remove participant') }}">
                                                    <x-heroicon-o-trash class="w-5 h-5" />
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Table Legend -->
                        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('Legend') }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div>
                                    <h5 class="text-xs font-medium text-gray-500 mb-1">{{ __('Removal Status') }}</h5>
                                    <div class="space-y-1">
                                        <div class="flex items-center">
                                            <span class="inline-block w-4 h-4 rounded-full bg-red-50 mr-2"></span>
                                            <span
                                                class="text-xs text-gray-600">{{ __('Red background: Cannot be removed (has discipline assigned)') }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <x-heroicon-o-lock-closed class="h-4 w-4 text-gray-400 mr-2" />
                                            <span
                                                class="text-xs text-gray-600">{{ __('Lock icon: Cannot be removed (has discipline assigned)') }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <x-heroicon-o-trash class="h-4 w-4 text-red-600 mr-2" />
                                            <span
                                                class="text-xs text-gray-600">{{ __('Trash icon: Can be removed (no discipline assigned)') }}</span>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Success Modal -->
    <div x-data="{ show: @entangle('showSuccessModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div
                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <x-heroicon-o-check class="h-6 w-6 text-green-600" />
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            {{ $successModalData['title'] ?? __('events.step1_complete_title') }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                {{ $successModalData['message'] ?? __('events.step1_complete_message') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 sm:mt-4 sm:flex sm:flex-col space-y-3">
                    @if (!empty($successModalData['nextSteps']))
                        @foreach ($successModalData['nextSteps'] as $step)
                            <a href="{{ $step['url'] }}"
                                @class([
                                    'w-full inline-flex justify-center rounded-md border shadow-sm px-4 py-2 text-base font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 sm:text-sm',
                                    'border-transparent bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500' => ($step['primary'] ?? false),
                                    'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-indigo-500' => !($step['primary'] ?? false),
                                ])>
                                {{ $step['label'] }}
                                @if ($step['primary'] ?? false)
                                    <x-heroicon-m-arrow-right class="ml-2 h-5 w-5" />
                                @endif
                            </a>
                        @endforeach
                    @else
                        <a href="{{ route($this->getRedirectRoute(), ['event' => $this->event]) }}"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm">
                            {{ __('Close') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Remove Confirmation Modal -->
    <div x-data="{ show: @entangle('showRemoveConfirmation') }" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <x-heroicon-o-exclamation-circle class="h-6 w-6 text-red-600" />
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            {{ __('Remove Participant') }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                {{ __('Are you sure you want to remove this participant? This action cannot be undone.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button wire:click="removeRegisteredParticipant" type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm">
                        {{ __('Remove') }}
                    </button>
                    <button wire:click="cancelRemoval" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div x-data="{
        show: false,
        title: '',
        message: '',
        errorDetails: ''
    }"
    x-on:show-error-modal.window="
        show = true;
        title = $event.detail[0]?.title || 'Error';
        message = $event.detail[0]?.message || 'An error occurred';
        errorDetails = $event.detail[0]?.errorDetails || '';
    "
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <x-heroicon-o-exclamation-circle class="h-6 w-6 text-red-600" />
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" x-text="title"></h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" x-html="message"></p>
                            <template x-if="errorDetails">
                                <div class="mt-2 p-2 bg-red-50 rounded text-xs text-red-700 font-mono" x-text="errorDetails"></div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button @click="show = false" type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
