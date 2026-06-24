@section('title', __('entities.title'))
<x-layout>
    <div>
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        {{ __('entities.title') }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ __('entities.manage_entity_associations') }}
                    </p>
                </div>

                <!-- Quick Stats -->
                @if($associatedEntities->isNotEmpty())
                    @php
                        $activeCount = $associatedEntities->filter(function($e) {
                            return $e->individualEntities->first()?->status_class === \Domain\Individuals\States\ActiveIndividualEntityState::class;
                        })->count();
                        $pendingCount = $associatedEntities->count() - $activeCount;
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-200 dark:border-emerald-800">
                            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                            <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">{{ $activeCount }}</span>
                            <span class="text-xs text-emerald-600 dark:text-emerald-500">{{ __('entities.status_active') }}</span>
                        </div>
                        @if($pendingCount > 0)
                            <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                                <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                                <span class="text-sm font-semibold text-amber-700 dark:text-amber-400">{{ $pendingCount }}</span>
                                <span class="text-xs text-amber-600 dark:text-amber-500">{{ __('entities.status_pending') }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Request to Join Entity Form -->
        <div class="mb-8">
            <livewire:individual-request-entity />
        </div>

        <!-- Associated Entities Section -->
        @if ($associatedEntities->isNotEmpty())
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50">
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('entities.designation') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('entities.id_number') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('entities.affiliate_number') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('entities.affiliation_status') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('entities.invitation_date') }}
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">{{ __('entities.actions') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($associatedEntities as $entity)
                                @php
                                    $individualEntity = $entity->individualEntities->first();
                                    $statusName = $individualEntity?->stateName() ?? 'Unknown';
                                    $translatedStatus = match($statusName) {
                                        'Active' => __('entities.status_active'),
                                        'Pending' => __('entities.status_pending'),
                                        'Pending Individual' => __('entities.status_pending'),
                                        'Pending Entity' => __('entities.status_pending_entity'),
                                        'Canceled' => __('entities.status_canceled'),
                                        'Denied' => __('entities.status_denied'),
                                        'Rejected' => __('entities.status_rejected'),
                                        default => $statusName
                                    };
                                    $isPendingFromIndividual = $individualEntity?->status_class === \Domain\Individuals\States\PendingFromIndividualEntityState::class;
                                    $isPendingFromEntity = $individualEntity?->status_class === \Domain\Individuals\States\PendingFromEntityIndividualEntityState::class;
                                    $isActive = $individualEntity?->status_class === \Domain\Individuals\States\ActiveIndividualEntityState::class;
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-slate-600 to-slate-800 dark:from-slate-500 dark:to-slate-700 flex items-center justify-center shadow-sm">
                                                <span class="text-white font-bold text-sm">
                                                    {{ strtoupper(substr($entity->name, 0, 2)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-slate-900 dark:text-white">
                                                    {{ $entity->name }}
                                                </div>
                                                @if($entity->country)
                                                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                                        <img
                                                            src="{{ asset('img/flags/'.strtolower($entity->country->iso).'.svg') }}"
                                                            alt="{{ $entity->country->name }}"
                                                            class="w-4 h-3 rounded shadow-sm"
                                                        >
                                                        {{ $entity->country->name }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono text-sm text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded">
                                            {{ $entity->member_code ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                        {{ $entity->member_number ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($individualEntity)
                                            <x-tables.badge :status="$translatedStatus" :color="$individualEntity->stateColor()" />
                                        @else
                                            <span class="text-sm text-slate-500 dark:text-slate-400">{{ __('entities.relationship_error') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($individualEntity?->created_at)
                                            <div class="text-sm text-slate-600 dark:text-slate-300">
                                                {{ $individualEntity->created_at->format('d/m/Y') }}
                                            </div>
                                            <div class="text-xs text-slate-400 dark:text-slate-500">
                                                {{ $individualEntity->created_at->diffForHumans() }}
                                            </div>
                                        @else
                                            <span class="text-sm text-slate-400 dark:text-slate-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- View Button -->
                                            <a
                                                href="{{ route('individual.entity.show', $entity) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                                                title="{{ __('entities.view') }}"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                {{ __('entities.view') }}
                                            </a>

                                            @if ($isPendingFromIndividual)
                                                <!-- Accept/Refuse for invitations from entity -->
                                                <form action="{{ route(Request::segment(1) . '.entity.approve') }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $entity->id }}">
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-sm transition-colors"
                                                        onclick="return confirm('{{ __('entities.confirm_accept_invitation', ['name' => $entity->name]) }}')"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        {{ __('entities.accept') }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('individual.entity.delete', $entity) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-white dark:bg-slate-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 text-rose-600 dark:text-rose-400 border border-slate-200 dark:border-slate-600 hover:border-rose-300 dark:hover:border-rose-800 rounded-lg transition-colors"
                                                        onclick="return confirm('{{ __('entities.confirm_refuse_invitation', ['name' => $entity->name]) }}')"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                        {{ __('entities.cancel') }}
                                                    </button>
                                                </form>
                                            @elseif($isPendingFromEntity)
                                                <!-- Pending your approval from entity - show waiting status and cancel option -->
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                                                    <svg class="w-3.5 h-3.5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ __('entities.pending_approval') }}
                                                </span>
                                                <form action="{{ route('individual.entity.delete', $entity) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors"
                                                        onclick="return confirm('{{ __('entities.confirm_cancel_request', ['name' => $entity->name]) }}')"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                        {{ __('entities.disassociate') }}
                                                    </button>
                                                </form>
                                            @elseif($isActive)
                                                <!-- Active relationship - only disassociate option -->
                                                <form action="{{ route('individual.entity.delete', $entity) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors"
                                                        onclick="return confirm('{{ __('entities.confirm_cancel_relationship', ['name' => $entity->name]) }}')"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                        </svg>
                                                        {{ __('entities.disassociate') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="text-center py-12 px-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-700 mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-1">
                        {{ __('entities.no_associations') }}
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                        {{ __('entities.no_associations_desc') }}
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-layout>
