<div class="mx-4 pt-6">
    {{-- Page header --}}
    <div class="sm:flex sm:justify-between sm:items-center mb-6">
        {{-- Left: Title --}}
        <div class="mb-4 sm:mb-0">
            <h1 class="page-first-title">
                Federation Voting Rights - {{ $year }}
            </h1>
        </div>

        {{-- Right: Actions --}}
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            {{-- Year Selector --}}
            <div class="flex items-center space-x-2">
                <label for="year-select" class="input-label">{{ __('Year') }}:</label>
                <select id="year-select" wire:model.live="year" name="year" class="input-select w-32">
                    @foreach ($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Export Button --}}
            <a href="{{ route('admin.federation-voting-right.export', ['year' => $year]) }}" class="btn btn-info">
                <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-1" />
                {{ __('Export') }}
            </a>
        </div>
    </div>

    {{-- Conditionally display the edit form --}}
    @if ($editingFederationId)
        <div class="mb-6">
            @livewire(
                'admin.federation-voting-right-form',
                [
                    'federationId' => $editingFederationId,
                    'year' => $year,
                ],
                key('edit-' . $editingFederationId . '-' . $year)
            )
        </div>
    @endif

    {{-- Voting Rights Table --}}
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-sm border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="table-auto w-full divide-y divide-gray-200 dark:divide-gray-700 dynamic-table">
                <thead
                    class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="p-2 whitespace-nowrap w-px">
                            <div class="font-semibold text-left">Federation</div>
                        </th>
                        <th class="p-2 whitespace-nowrap w-px">
                            <div class="font-semibold text-center">Actions</div>
                        </th>
                        <th class="p-2 whitespace-nowrap" title="General Assembly">
                            <div class="font-semibold text-center">GA</div>
                        </th>
                        <th class="p-2 whitespace-nowrap" title="Technical Committee">
                            <div class="font-semibold text-center">Tech</div>
                        </th>
                        <th class="p-2 whitespace-nowrap" title="Scientific Committee">
                            <div class="font-semibold text-center">Sci</div>
                        </th>
                        <th class="p-2 whitespace-nowrap" title="Sport Committee">
                            <div class="font-semibold text-center">Sport</div>
                        </th>
                        {{-- Sport Commissions --}}
                        <th class="p-2 whitespace-nowrap" title="Finswimming Commission">
                            <div class="font-semibold text-center">Finswim</div>
                        </th>
                        <th class="p-2 whitespace-nowrap" title="Freediving Commission">
                            <div class="font-semibold text-center">Freedive</div>
                        </th>
                        <th class="p-2 whitespace-nowrap" title="Aquathlon Commission">
                            <div class="font-semibold text-center">Aquathlon</div>
                        </th>
                        <th class="p-2 whitespace-nowrap" title="Underwater Hockey Commission">
                            <div class="font-semibold text-center">UW Hockey</div>
                        </th>
                        <th class="p-2 whitespace-nowrap" title="Underwater Rugby Commission">
                            <div class="font-semibold text-center">UW Rugby</div>
                        </th>
                        <th class="p-2 whitespace-nowrap" title="Target Shooting Commission">
                            <div class="font-semibold text-center">Target Shoot</div>
                        </th>
                        <th class="p-2 whitespace-nowrap" title="Sport Diving Commission">
                            <div class="font-semibold text-center">Sport Dive</div>
                        </th>
                        <th class="p-2 whitespace-nowrap" title="Spearfishing Commission">
                            <div class="font-semibold text-center">Spearfish</div>
                        </th>
                        <th class="p-2 whitespace-nowrap" title="Orienteering Commission">
                            <div class="font-semibold text-center">Orient</div>
                        </th>
                        <th class="p-2 whitespace-nowrap" title="Visual Commission">
                            <div class="font-semibold text-center">Visual</div>
                        </th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($federations as $federation)
                        @php
                            $votingRight = $federation->votingRights->first();
                            $isEditing = $editingFederationId === $federation->id;

                            // Helper function to get status color class
                            $getStatusClasses = function ($status) {
                                return match ($status) {
                                    \Domain\Federations\Models\FederationVotingRight::STATUS_VOTING_RIGHT
                                        => 'bg-green-100 dark:bg-green-800/30 text-green-800 dark:text-green-300',
                                    \Domain\Federations\Models\FederationVotingRight::STATUS_SUSPENDED
                                        => 'bg-red-100 dark:bg-red-800/30 text-red-800 dark:text-red-300',
                                    \Domain\Federations\Models\FederationVotingRight::STATUS_PROBATION
                                        => 'bg-yellow-100 dark:bg-yellow-800/30 text-yellow-800 dark:text-yellow-300',
                                    \Domain\Federations\Models\FederationVotingRight::STATUS_NO_VOTING_RIGHT
                                        => 'bg-red-100 text-gray-600',
                                    default => 'text-gray-500 dark:text-gray-400', // Fallback for null/empty
                                };
                            };
                        @endphp
                        <tr
                            class="{{ $isEditing ? 'bg-indigo-50 dark:bg-indigo-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                            <td class="p-2">
                                <div class="font-medium text-gray-800 dark:text-gray-100">
                                    <span
                                        class="text-gray-500 dark:text-gray-400 mr-1">{{ $federation->member_code }}</span>
                                    - {{ $federation->name }}
                                </div>
                            </td>
                            <td class="p-2 whitespace-nowrap w-px">
                                <div class="text-center">
                                    @if (!$isEditing)
                                        <button type="button" wire:click="editVotingRights({{ $federation->id }})"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200 focus:outline-none"
                                            title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                                            Editing
                                        </span>
                                    @endif
                                </div>
                            </td>
                            {{-- Status Cells with Color Coding --}}
                            <td
                                class="p-2 whitespace-nowrap text-center font-medium {{ $getStatusClasses($votingRight?->general_assembly_status) }}">
                                {{ $votingRight?->general_assembly_status ?? 'N/A' }}</td>
                            <td
                                class="p-2 whitespace-nowrap text-center font-medium {{ $getStatusClasses($votingRight?->technical_committee_status) }}">
                                {{ $votingRight?->technical_committee_status ?? 'N/A' }}</td>
                            <td
                                class="p-2 whitespace-nowrap text-center font-medium {{ $getStatusClasses($votingRight?->scientific_committee_status) }}">
                                {{ $votingRight?->scientific_committee_status ?? 'N/A' }}</td>
                            <td
                                class="p-2 whitespace-nowrap text-center font-medium {{ $getStatusClasses($votingRight?->sport_committee_status) }}">
                                {{ $votingRight?->sport_committee_status ?? 'N/A' }}</td>
                            <td
                                class="p-2 whitespace-nowrap text-center font-medium {{ $getStatusClasses($votingRight?->finswimming_commission_status) }}">
                                {{ $votingRight?->finswimming_commission_status ?? 'N/A' }}</td>
                            <td
                                class="p-2 whitespace-nowrap text-center font-medium {{ $getStatusClasses($votingRight?->freediving_commission_status) }}">
                                {{ $votingRight?->freediving_commission_status ?? 'N/A' }}</td>
                            <td
                                class="p-2 whitespace-nowrap text-center font-medium {{ $getStatusClasses($votingRight?->aquathlon_commission_status) }}">
                                {{ $votingRight?->aquathlon_commission_status ?? 'N/A' }}</td>
                            <td
                                class="p-2 whitespace-nowrap text-center font-medium {{ $getStatusClasses($votingRight?->underwater_hockey_commission_status) }}">
                                {{ $votingRight?->underwater_hockey_commission_status ?? 'N/A' }}</td>
                            <td
                                class="p-2 whitespace-nowrap text-center font-medium {{ $getStatusClasses($votingRight?->underwater_rugby_commission_status) }}">
                                {{ $votingRight?->underwater_rugby_commission_status ?? 'N/A' }}</td>
                            <td
                                class="p-2 whitespace-nowrap text-center font-medium {{ $getStatusClasses($votingRight?->target_shooting_commission_status) }}">
                                {{ $votingRight?->target_shooting_commission_status ?? 'N/A' }}</td>
                            <td
                                class="p-2 whitespace-nowrap text-center font-medium {{ $getStatusClasses($votingRight?->sport_diving_commission_status) }}">
                                {{ $votingRight?->sport_diving_commission_status ?? 'N/A' }}</td>
                            <td
                                class="p-2 whitespace-nowrap text-center font-medium {{ $getStatusClasses($votingRight?->spearfishing_commission_status) }}">
                                {{ $votingRight?->spearfishing_commission_status ?? 'N/A' }}</td>
                            <td
                                class="p-2 whitespace-nowrap text-center font-medium {{ $getStatusClasses($votingRight?->orienteering_commission_status) }}">
                                {{ $votingRight?->orienteering_commission_status ?? 'N/A' }}</td>
                            <td
                                class="p-2 whitespace-nowrap text-center font-medium {{ $getStatusClasses($votingRight?->visual_commission_status) }}">
                                {{ $votingRight?->visual_commission_status ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                No federations found for the year {{ $year }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div> {{-- End single root element --}}
