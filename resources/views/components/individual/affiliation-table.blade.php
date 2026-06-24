@props(['affiliations', 'showActions' => false])

@php
use Illuminate\Support\Str;
@endphp

<div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliations.active_affiliations') }}</h3>
            </div>
            <span class="text-sm text-gray-500">
                {{ trans_choice('affiliations.affiliation_count', $affiliations->count(), ['count' => $affiliations->count()]) }}
            </span>
        </div>
    </div>
    
    @if($affiliations->isEmpty())
        <div class="px-6 py-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p class="mt-4 text-sm text-gray-500">{{ __('affiliations.no_active_affiliations') }}</p>
        </div>
    @else
        <div class="overflow-x-auto relative">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">
                            {{ __('affiliations.federation') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">
                            {{ __('affiliations.plan') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('affiliations.period') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('affiliations.status') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('affiliations.fee') }}
                        </th>
                        @if($showActions)
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">{{ __('main.actions') }}</span>
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($affiliations as $affiliation)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($affiliation->federation && $affiliation->federation->hasMedia('flag'))
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-lg object-cover" 
                                                 src="{{ $affiliation->federation->getFirstMediaUrl('flag', 'thumb') }}" 
                                                 alt="{{ $affiliation->federation->name }}">
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-indigo-100 rounded-lg">
                                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="ml-4 min-w-0 flex-1">
                                        <div class="text-sm font-medium text-gray-900 truncate" title="{{ $affiliation->federation->name ?? __('affiliations.no_federation') }}">
                                            {{ $affiliation->federation->name ?? __('affiliations.no_federation') }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $affiliation->federation->code ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="min-w-0">
                                    @php
                                        $affiliationPlan = null;
                                        try {
                                            if ($affiliation->relationLoaded('memberSubscription')) {
                                                $affiliationPlan = $affiliation->getAffiliationPlanAttribute();
                                            }
                                        } catch (\Exception $e) {
                                            // Handle if relationship is not loaded
                                            $affiliationPlan = null;
                                        }
                                    @endphp
                                    <div class="text-sm text-gray-900 truncate" title="{{ $affiliationPlan ? $affiliationPlan->name : __('affiliations.standard_plan') }}">
                                        @if($affiliationPlan)
                                            {{ $affiliationPlan->name }}
                                        @else
                                            {{ __('affiliations.standard_plan') }}
                                        @endif
                                    </div>
                                    @if($affiliationPlan && $affiliationPlan->description)
                                        <div class="text-sm text-gray-500 truncate" title="{{ $affiliationPlan->description }}">
                                            {{ Str::limit($affiliationPlan->description, 50) }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $affiliation->start_date->format('d/m/Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ __('affiliations.until') }} {{ $affiliation->end_date->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($affiliation->isActive())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        {{ __('affiliations.active') }}
                                    </span>
                                @elseif($affiliation->state->name() === 'Expired')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-red-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        {{ __('affiliations.expired') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        {{ $affiliation->stateName() }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    €{{ number_format($affiliation->individual_fee ?? 0, 2) }}
                                </div>
                                @if($affiliationPlan ?? false)
                                    <div class="text-sm text-gray-500">
                                        {{ $affiliationPlan->period ?? 'Annual' }}
                                    </div>
                                @endif
                            </td>

                            @if($showActions)
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900">{{ __('main.view') }}</a>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>