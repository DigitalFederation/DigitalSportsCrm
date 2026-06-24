@section('title', __('licenses.International License Purchase Success'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('licenses.International License Purchase Success') }}</h1>
            </div>
        </div>

        <!-- Success Message -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
            <div class="flex">
                <svg class="w-6 h-6 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-medium text-green-800">{{ __('licenses.Purchase Initiated Successfully') }}</h3>
                    <p class="text-sm text-green-700 mt-1">
                        {{ __('licenses.Your international license purchase has been initiated. Please complete the payment to activate your license.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- License Details -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('licenses.International License Details') }}</h2>

            <!-- international Badge -->
            <div class="mb-4 flex items-center">
                <span class="text-sm text-blue-600 font-medium">{{ __('International License') }}</span>
            </div>

            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('licenses.License Name') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $license->name }}</dd>
                </div>

                @if($license->sport)
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('licenses.Sport') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $license->sport->name }}</dd>
                </div>
                @endif

                @if($license->professionalRole)
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('licenses.Professional Role') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $license->professionalRole->name }}</dd>
                </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('licenses.Status') }}</dt>
                    <dd class="mt-1">
                        @if($licenseAttributed->status_class === 'Domain\\Licenses\\States\\ActiveLicenseAttributedState')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ __('licenses.Active') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                {{ __('licenses.Pending Payment') }}
                            </span>
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('licenses.License Holder') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $individual->full_name }}</dd>
                </div>

                @if($licenseAttributed->total_value)
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('licenses.Amount') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">€ {{ number_format($licenseAttributed->total_value, 2, ',', '.') }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <!-- Document Section -->
        @if($document)
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('licenses.Invoice') }}</h2>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">{{ __('licenses.Document Number') }}: {{ $document->number }}</p>
                    <p class="text-sm text-gray-600">{{ __('licenses.Date') }}: {{ $document->created_at->format('d/m/Y') }}</p>
                </div>
                <a href="{{ route('individual.document.download', $document->id) }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="mr-2 -ml-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    {{ __('licenses.Download Invoice') }}
                </a>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="flex justify-between">
            <a href="{{ route('individual.international-license-purchase.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                ← {{ __('licenses.Back to International Licenses') }}
            </a>

            <a href="{{ route('individual.international-licenses-attributed.index') }}"
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('licenses.View My International Licenses') }} →
            </a>
        </div>

    </div>
</x-layout>