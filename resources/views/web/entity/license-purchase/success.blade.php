@section('title', __('licenses.Purchase Successful'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="page-first-title text-green-800">{{ __('licenses.Purchase Successful!') }}</h1>
            <p class="text-slate-600">{{ __('licenses.Your license purchase has been completed successfully') }}</p>
        </div>

        <!-- Purchase Details -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg border border-slate-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('licenses.order_details') }}</h3>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('licenses.Order Number') }}:</dt>
                        <dd class="font-medium">#{{ $document->reference ?? 'N/A' }}</dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('licenses.License') }}:</dt>
                        <dd class="font-medium">{{ $license->name ?? 'N/A' }}</dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('licenses.Purchase Type') }}:</dt>
                        <dd class="font-medium">
                            {{ $purchaseType === 'members' ? __('licenses.Member Licenses') : __('licenses.Entity License') }}
                        </dd>
                    </div>

                    @if($purchaseType === 'members' && !empty($memberCount))
                        <div class="flex justify-between">
                            <dt class="text-slate-600">{{ __('licenses.Members') }}:</dt>
                            <dd class="font-medium">{{ $memberCount }} {{ __('licenses.members') }}</dd>
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('licenses.Total Amount') }}:</dt>
                        <dd class="font-medium text-green-600">€{{ number_format($totalAmount ?? 0, 2) }}</dd>
                    </div>

                </dl>
            </div>


            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">

                @if($document)
                    <a href="{{ route(Request::segment(1).'.document.show', $document->id) }}"
                       class="btn btn-secondary">
                        {{ __('licenses.Download Invoice') }}
                    </a>
                @endif

                <a href="{{ route(Request::segment(1).'.dashboard') }}"
                   class="btn btn-secondary">
                    {{ __('main.Back') }}
                </a>
            </div>
        </div>

    </div>
</x-layout>