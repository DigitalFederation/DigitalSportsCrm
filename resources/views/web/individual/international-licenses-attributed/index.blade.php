@extends('layouts.app')
@section('title', __('licenses.international_licenses'))

@section('content')
<div class="previous-layout-classes">

    <!-- International Header -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex items-center">
        <div>
            <h2 class="text-lg font-semibold text-blue-900">{{ __('licenses.cmas_international_licenses') }}</h2>
            <p class="text-sm text-blue-700">{{ __('licenses.international_licenses_description') }}</p>
        </div>
    </div>

    <!-- Page Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="page-first-title">{{ $title }}</h1>
        </div>

        <div class="flex space-x-3">
            <a href="{{ route('individual.license-attributed.index') }}"
               class="text-sm text-blue-600 hover:text-blue-700 underline">
                {{ __('licenses.view_national_licenses') }}
            </a>
            <a href="{{ route('individual.international-license-purchase.index') }}"
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                {{ __('licenses.purchase_international_license') }}
            </a>
        </div>
    </div>

    <!-- Filters -->
    <x-filter-bar :filters="[
        'filter[filter_status]' => [
            'label' => __('Status'),
            'options' => $filter_status
        ],
        'filter[filter_sport]' => [
            'label' => __('Sport'),
            'options' => $sports
        ],
        'filter[filter_category]' => [
            'label' => __('Category'),
            'options' => $professional_roles
        ],
        'filter[filter_federation]' => [
            'label' => __('Federation'),
            'options' => $federations
        ],
    ]" />

    <!-- Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        @if($licenses->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('licenses.license') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('licenses.federation') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('licenses.sport_category') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('licenses.status') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('licenses.validity') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('licenses.international_code') }}
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">{{ __('licenses.actions') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($licenses as $license)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $license->license_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $license->holder_name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $license->federation->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $license->license->sport->translated_name ?? '-' }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $license->license->professionalRole->name ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($license->isActive())
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ __('licenses.active') }}
                                    </span>
                                @elseif($license->isPending())
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        {{ __('licenses.pending') }}
                                    </span>
                                @elseif($license->isCancelled())
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ __('licenses.cancelled') }}
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ __('licenses.unknown') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($license->current_term_starts_at && $license->current_term_ends_at)
                                    {{ $license->current_term_starts_at->format('d/m/Y') }} -
                                    {{ $license->current_term_ends_at->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-blue-600">
                                    {{ $license->license_number ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    @if($license->isActive())
                                        <a href="{{ route('individual.license-attributed.show', $license->id) }}"
                                           class="text-indigo-600 hover:text-indigo-900">
                                            {{ __('licenses.view') }}
                                        </a>
                                    @endif
                                    @if($license->isPending())
                                        <a href="{{ route('individual.documents.index', ['filter[owner_type]' => 'LicenseAttributed', 'filter[owner_id]' => $license->id]) }}"
                                           class="text-blue-600 hover:text-blue-900">
                                            {{ __('licenses.documents') }}
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $licenses->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('licenses.no_international_licenses') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('licenses.no_international_licenses_message') }}</p>
                <div class="mt-6">
                    <a href="{{ route('individual.international-license-purchase.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        {{ __('licenses.purchase_international_license') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection