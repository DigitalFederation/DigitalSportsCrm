@section('title', $entity->name . ' - ' . __('diving.diving_entity_details'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ $entity->name }}</h1>
                <p class="text-sm text-slate-600 mt-1">{{ __('diving.diving_entity_details') }}</p>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                @php
                    $isAssociated = auth()->user()->individual->entities()
                        ->where('entity_id', $entity->id)
                        ->exists();
                @endphp
                
                @if(!$isAssociated)
                    <form action="{{ route('individual.diving_entities.store') }}" method="POST" class="inline-block">
                        @csrf
                        <input type="hidden" name="entity_id" value="{{ $entity->id }}">
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0 mr-2" viewBox="0 0 16 16">
                                <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"/>
                            </svg>
                            <span>{{ __('diving.request_to_join') }}</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Entity Details Card -->
        <div class="card">
            <div class="mb-6">
                <h2 class="text-xl leading-snug text-slate-800 font-bold mb-5">{{ __('diving.entity_details') }}</h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('entities.entity_name') }}</label>
                    <p class="text-slate-600">{{ $entity->name }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('entities.affiliated_number') }}</label>
                    <p class="text-slate-600">{{ $entity->code ?: __('common.not_available') }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('entities.tax_number') }}</label>
                    <p class="text-slate-600">{{ $entity->tax_number ?: __('common.not_available') }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('entities.entity_email') }}</label>
                    <p class="text-slate-600">
                        @if($entity->email)
                            <a href="mailto:{{ $entity->email }}" class="text-blue-600 hover:text-blue-700">{{ $entity->email }}</a>
                        @else
                            {{ __('common.not_available') }}
                        @endif
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('entities.entity_phone') }}</label>
                    <p class="text-slate-600">{{ $entity->phone ?: __('common.not_available') }}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('entities.address') }}</label>
                    <p class="text-slate-600">
                        @if($entity->address || $entity->postal_code || $entity->city)
                            {{ $entity->address }}{{ $entity->address && ($entity->postal_code || $entity->city) ? ', ' : '' }}{{ $entity->postal_code }}{{ $entity->postal_code && $entity->city ? ' ' : '' }}{{ $entity->city }}
                        @else
                            {{ __('common.not_available') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Diving Service Provider Licenses Card -->
        <div class="card-no-padding mt-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-snug text-slate-800 font-bold">{{ __('diving.diving_service_provider_licenses') }}</h3>
            </div>

            @php
                $individual = auth()->user()->individual;
                $isTechnicalDirector = false;
                $technicalDirectorAssignments = collect();

                if ($individual) {
                    // Check if user is a technical director for this entity
                    $isTechnicalDirector = \Domain\Diving\Models\DivingEntityTechnicalDirector::where('entity_id', $entity->id)
                        ->where('individual_id', $individual->id)
                        ->where('status_class', \Domain\Diving\States\AssignedDivingTechnicalDirectorState::class)
                        ->exists();

                    // If technical director, get their assignments with licenses
                    if ($isTechnicalDirector) {
                        $technicalDirectorAssignments = \Domain\Diving\Models\DivingEntityTechnicalDirector::where('entity_id', $entity->id)
                            ->where('individual_id', $individual->id)
                            ->where('status_class', \Domain\Diving\States\AssignedDivingTechnicalDirectorState::class)
                            ->with(['licenseAttributed', 'licenseAttributed.license'])
                            ->get();
                    }
                }

                // Get all diving licenses for non-technical directors
                if (!$isTechnicalDirector) {
                    $divingLicenses = $entity->licenses()->whereHas('license', function($q) {
                        $q->where('committee_id', 3);
                    })->with('license')->get();
                }
            @endphp
            
            @if($isTechnicalDirector && $technicalDirectorAssignments->count() > 0)
                {{-- Display for Technical Directors --}}
                <div class="overflow-x-auto">
                    <table class="table-auto w-full divide-y divide-slate-200">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('licenses.license_type') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('licenses.acceptance_date') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('licenses.expiration_date') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('common.status') }}</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            @foreach($technicalDirectorAssignments as $assignment)
                                @if($assignment->licenseAttributed)
                                    <tr class="table-row">
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            {{ $assignment->licenseAttributed->license->name ?? $assignment->licenseAttributed->license_name ?? __('common.not_available') }}
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            {{ $assignment->assigned_at ? $assignment->assigned_at->format('d/m/Y') : __('common.not_applicable') }}
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            {{ $assignment->licenseAttributed->current_term_ends_at ? $assignment->licenseAttributed->current_term_ends_at->format('d/m/Y') : __('common.not_applicable') }}
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            @php
                                                $statusColor = match($assignment->licenseAttributed->state->name()) {
                                                    'active' => 'bg-emerald-100 text-emerald-800',
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'pending_validation' => 'bg-yellow-100 text-yellow-800',
                                                    'expired' => 'bg-rose-100 text-rose-800',
                                                    'suspended' => 'bg-red-100 text-red-800',
                                                    'canceled' => 'bg-gray-100 text-gray-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                            @endphp
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                                {{ __('licenses.states.' . $assignment->licenseAttributed->state->name()) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif(!$isTechnicalDirector && isset($divingLicenses) && $divingLicenses->count() > 0)
                {{-- Display for Regular Users --}}
                <div class="overflow-x-auto">
                    <table class="table-auto w-full divide-y divide-slate-200">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('licenses.license_type') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('licenses.license_number') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('licenses.acceptance_date') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('licenses.expiration_date') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('common.status') }}</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            @foreach($divingLicenses as $license)
                                <tr class="table-row">
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        {{ $license->license->name }}
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        {{ $license->license_number ?? __('common.not_available') }}
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        {{ $license->activated_at ? $license->activated_at->format('d/m/Y') : __('common.not_applicable') }}
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        {{ $license->current_term_ends_at ? $license->current_term_ends_at->format('d/m/Y') : __('common.not_applicable') }}
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @php
                                            $statusColor = match($license->state->name()) {
                                                'active' => 'bg-emerald-100 text-emerald-800',
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'pending_validation' => 'bg-yellow-100 text-yellow-800',
                                                'expired' => 'bg-rose-100 text-rose-800',
                                                'suspended' => 'bg-red-100 text-red-800',
                                                'canceled' => 'bg-gray-100 text-gray-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                            {{ __('licenses.states.' . $license->state->name()) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-4 py-12 text-center">
                    <div class="text-slate-500">{{ __('diving.no_diving_licenses') }}</div>
                </div>
            @endif
        </div>

    </div>
</x-layout>