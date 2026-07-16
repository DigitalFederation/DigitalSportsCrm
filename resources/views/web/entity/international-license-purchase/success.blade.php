@section('title', __('International Purchase Successful'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- International Header -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex items-center">
            <div>
                <h2 class="text-lg font-semibold text-blue-900">{{ __('International License') }}</h2>
                <p class="text-sm text-blue-700">{{ __('Your international license is now active') }}</p>
            </div>
        </div>

        <!-- Page header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="page-first-title text-green-800">{{ __('International Purchase Successful!') }}</h1>
            <p class="text-slate-600">{{ __('Your international license purchase has been completed successfully') }}</p>
        </div>

        <!-- Purchase Details -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg border border-slate-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Purchase Details') }}</h3>
                
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('Order Number') }}:</dt>
                        <dd class="font-medium">#{{ $document->reference ?? 'N/A' }}</dd>
                    </div>
                    
                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('International License') }}:</dt>
                        <dd class="font-medium">{{ $license->name ?? 'N/A' }}</dd>
                    </div>
                    
                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('License Type') }}:</dt>
                        <dd class="font-medium">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">{{ __('International') }}</span>
                        </dd>
                    </div>
                    
                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('Purchase Type') }}:</dt>
                        <dd class="font-medium">
                            {{ $purchaseType === 'members' ? __('Member Licenses') : __('Entity License') }}
                        </dd>
                    </div>
                    
                    @if($purchaseType === 'members' && !empty($memberCount))
                        <div class="flex justify-between">
                            <dt class="text-slate-600">{{ __('Members') }}:</dt>
                            <dd class="font-medium">{{ $memberCount }} {{ __('members') }}</dd>
                        </div>
                    @endif
                    
                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('Total Amount') }}:</dt>
                        <dd class="font-medium text-green-600">{{ money($totalAmount ?? 0, $document?->currency) }}</dd>
                    </div>

                </dl>
            </div>

            <!-- Next Steps -->
            <div class="bg-blue-50 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-blue-800 mb-4">{{ __('What happens next?') }}</h3>
                <ul class="space-y-2 text-blue-700">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ $purchaseType === 'members' ? __('All selected members have been automatically licensed with international licenses') : __('Your entity\'s international license has been automatically activated') }}
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ __('International license certificates are now available for download') }}
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ __('Your international licenses are recognized internationally by all member federations') }}
                    </li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route(Request::segment(1).'.international-license-attributed.index') }}" 
                   class="btn btn-primary">
                    {{ __('View International Licenses') }}
                </a>
                
                @if($document)
                    <a href="{{ route(Request::segment(1).'.document.show', $document->id) }}" 
                       class="btn btn-secondary">
                        {{ __('Download Invoice') }}
                    </a>
                @endif
                
                <a href="{{ route(Request::segment(1).'.dashboard') }}" 
                   class="btn btn-secondary">
                    {{ __('Back to Dashboard') }}
                </a>
            </div>
        </div>

    </div>
</x-layout>