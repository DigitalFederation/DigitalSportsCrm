@section('title', __('diving.diving_certifications'))
<x-layout>
    <div class="previous-layout-classes">
        
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('diving.diving_certifications') }}</h1>
                <p class="text-sm text-slate-600 mt-1">{{ __('diving.manage_non_cmas') }}</p>
            </div>
            
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('individual.diving_certifications.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    {{ __('diving.add_certification') }}
                </a>
            </div>
        </div>


        <!-- Table Card -->
        <div class="card-no-padding">
            <div class="overflow-x-auto">
                <table class="table-auto w-full divide-y divide-slate-200">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('diving.certification') }}</div></th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('diving.system') }}</div></th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('diving.number') }}</div></th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('diving.issue_date') }}</div></th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('diving.status') }}</div></th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-right">{{ __('diving.actions') }}</div></th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                            @forelse ($certifications as $certification)
                                <tr>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium text-slate-800">
                                            {{ $certification->certification_name }}
                                        </div>
                                        <div class="text-sm text-slate-600">
                                            {{ __('diving.' . $certification->certification_level) !== 'diving.' . $certification->certification_level ? __('diving.' . $certification->certification_level) : $certification->certification_level }}
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                            {{ $certification->certification_system }}
                                        </span>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="text-sm text-slate-800">
                                            {{ $certification->certification_number }}
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="text-sm text-slate-800">
                                            {{ $certification->issue_date->format('d/m/Y') }}
                                        </div>
                                        @if($certification->expiration_date)
                                            <div class="text-xs text-slate-600">
                                                {{ __('diving.expires') }}: {{ $certification->expiration_date->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if($certification->isActive())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                                {{ __('diving.active') }}
                                            </span>
                                        @elseif($certification->isPendingValidation())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                {{ __('diving.pending_validation') }}
                                            </span>
                                        @elseif($certification->isExpired())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ __('diving.expired') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                                {{ $certification->state->name() }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="space-x-1 flex justify-end items-end">
                                            <a href="{{ route('individual.diving_certifications.show', $certification) }}" class="btn btn-secondary btn-sm">{{ __('diving.view') }}</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-2 first:pl-5 last:pr-5 py-12 text-center">
                                        <div class="text-slate-500">{{ __('main.no_certifications') }}</div>
                                        <div class="mt-4">
                                            <a href="{{ route('individual.diving_certifications.create') }}" class="btn btn-primary btn-sm">
                                                {{ __('diving.upload_certification') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($certifications->hasPages())
                    <div class="card-footer">
                        {{ $certifications->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-layout>
