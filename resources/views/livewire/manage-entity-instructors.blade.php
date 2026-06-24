<div class="space-y-6">

    {{-- Section 1: Associated Instructors --}}
    @if($showAssociatedSection)
    <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('diving.associated_instructors') }}</h2>

        @if ($this->associatedInstructors->isEmpty())
            <p class="text-gray-500 dark:text-gray-400">{{ __('No instructors are currently associated with this entity for the selected roles.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ __('International Code') }}</th>
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ __('Name') }}</th>
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ __('Country') }}</th>
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ __('Status') }}</th>
                        <th scope="col" class="relative px-4 py-3">
                            <span class="sr-only">{{ __('Actions') }}</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @foreach ($this->associatedInstructors as $association)
                        @php
                            // Extract status details (assuming state classes like ActiveEntityProfessionalRoleState)
                            $statusClassBase = class_basename($association->status_class);
                            $statusText = Str::headline(str_replace('EntityProfessionalRoleState', '', $statusClassBase));
                            $statusColor = match ($statusClassBase) {
                                'ActiveEntityProfessionalRoleState' => 'text-green-600 dark:text-green-400',
                                'PendingEntityProfessionalRoleState' => 'text-yellow-600 dark:text-yellow-400',
                                'RejectedEntityProfessionalRoleState' => 'text-red-600 dark:text-red-400',
                                'CanceledEntityProfessionalRoleState' => 'text-gray-500 dark:text-gray-400',
                                default => 'text-gray-700 dark:text-gray-300',
                            };
                        @endphp
                        <tr>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $association->individual?->member_code ?? 'N/A' }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $association->individual?->full_name ?? 'N/A' }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($association->individual?->country?->iso)
                                    <img
                                        src="{{ asset('img/flags/' . strtolower($association->individual->country->iso) . '.svg') }}"
                                        alt="{{ $association->individual->country->name }}"
                                        class="inline-block h-4 w-6 mr-2" loading="lazy">
                                    {{ $association->individual->country->name }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold {{ $statusColor }}">{{ $statusText }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button
                                    wire:click="removeAssociation({{ $association->id }})"
                                    wire:confirm="{{ __('Are you sure you want to remove this association?') }}"
                                    type="button"
                                    class="text-red-600 hover:text-red-900 dark:text-red-500 dark:hover:text-red-400"
                                    title="{{ __('Remove Association') }}"
                                >
                                    {{ __('Remove') }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @endif

    {{-- Section 2: Invite Instructor or Leader --}}
    @if($showInviteSection)
    <div>
        {{-- Render the Filament table for inviting --}}
        {{ $this->table }}
    </div>
    @endif

</div>
