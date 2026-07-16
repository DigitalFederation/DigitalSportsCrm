@section('title', __('License Purchased'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l3 3 8-8"></path>
                </svg>
            </div>
            @if($licenseAttributed->status_class === 'Domain\\Licenses\\States\\ActiveLicenseAttributedState')
                <h1 class="page-first-title text-green-800">{{ __('licenses.License Purchased Successfully!') }}</h1>
                <p class="text-slate-600">{{ __('licenses.Your license has been activated and is ready to use') }}</p>
            @else
                <h1 class="page-first-title text-blue-800">{{ __('licenses.License Purchase Initiated!') }}</h1>
                <p class="text-slate-600">{{ __('licenses.Your license purchase is being processed. You will receive a confirmation once payment is complete.') }}</p>
            @endif
        </div>

        <!-- License Details -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg border border-slate-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('License Information') }}</h3>
                
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('License') }}:</dt>
                        <dd class="font-medium">{{ $license->name ?? 'N/A' }}</dd>
                    </div>
                    
                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('License Code') }}:</dt>
                        <dd class="font-medium">{{ $license->license_code ?? 'N/A' }}</dd>
                    </div>
                    
                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('License Holder') }}:</dt>
                        <dd class="font-medium">{{ $individual->full_name ?? 'N/A' }}</dd>
                    </div>
                    
                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('International Code') }}:</dt>
                        <dd class="font-medium">{{ $licenseAttributed->license_number ?? __('Pending Assignment') }}</dd>
                    </div>
                    
                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('Issue Date') }}:</dt>
                        <dd class="font-medium">{{ $licenseAttributed->activated_at ? \Carbon\Carbon::parse($licenseAttributed->activated_at)->format('d/m/Y') : __('Today') }}</dd>
                    </div>
                    
                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('Expiration Date') }}:</dt>
                        <dd class="font-medium">{{ $licenseAttributed->date_expire ? \Carbon\Carbon::parse($licenseAttributed->date_expire)->format('d/m/Y') : __('Permanent') }}</dd>
                    </div>
                    
                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('Total Paid') }}:</dt>
                        <dd class="font-medium text-green-600">{{ money($totalAmount ?? 0, isset($document) ? $document->currency : null) }}</dd>
                    </div>
                    
                    <div class="flex justify-between">
                        <dt class="text-slate-600">{{ __('licenses.Status') }}:</dt>
                        @if($licenseAttributed->status_class === 'Domain\\Licenses\\States\\ActiveLicenseAttributedState')
                            <dd class="font-medium text-green-600">{{ __('licenses.Active') }}</dd>
                        @else
                            <dd class="font-medium text-orange-600">{{ __('licenses.Pending Payment') }}</dd>
                        @endif
                    </div>
                </dl>
            </div>

            @if($licenseAttributed->status_class === 'Domain\\Licenses\\States\\ActiveLicenseAttributedState')
                <!-- Certificate Information -->
                <div class="bg-blue-50 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-blue-800 mb-4">{{ __('licenses.Your License Certificate') }}</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-blue-700">{{ __('licenses.Your license certificate is now available for download') }}</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-blue-700">{{ __('licenses.You can view and manage your license in the My Licenses section') }}</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-blue-700">{{ __('licenses.A confirmation email has been sent to your registered email address') }}</span>
                        </div>
                    </div>
                </div>
            @else
                <!-- Payment Required Information -->
                <div class="bg-orange-50 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-orange-800 mb-4">{{ __('licenses.Payment Required') }}</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-orange-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-orange-700">{{ __('licenses.Your license is pending payment to be activated') }}</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-orange-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-orange-700">{{ __('licenses.Please complete the payment to activate your license and download the certificate') }}</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-orange-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-orange-700">{{ __('licenses.An invoice has been generated and is available for download') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Next Steps -->
            @if($license->professional_role || $license->sport)
                <div class="bg-amber-50 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-amber-800 mb-4">{{ __('Important Information') }}</h3>
                    <div class="space-y-2 text-amber-700">
                        @if($license->professional_role)
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ __('This license certifies you for: :role', ['role' => $license->professional_role->name]) }}</span>
                            </div>
                        @endif
                        @if($license->sport)
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ __('Valid for sport: :sport', ['sport' => $license->sport->name]) }}</span>
                            </div>
                        @endif
                        @if($licenseAttributed->date_expire)
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ __('Remember to renew before expiration date') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('individual.license-attributed.index') }}" 
                   class="btn btn-primary">
                    {{ __('licenses.View My Licenses') }}
                </a>
                
                @if(isset($document))
                    <a href="{{ route('individual.document.show', $document->id) }}" 
                       class="btn btn-secondary">
                        {{ __('licenses.Download Invoice') }}
                    </a>
                @endif
                
                @if($licenseAttributed->status_class === 'Domain\\Licenses\\States\\ActiveLicenseAttributedState')
                    <a href="{{ route('individual.certification-card.show', $licenseAttributed->id) }}" 
                       class="btn btn-secondary">
                        {{ __('licenses.Download Certificate') }}
                    </a>
                @else
                    <button type="button" class="btn btn-primary" onclick="alert('{{ __('licenses.Payment integration coming soon') }}')">
                        {{ __('licenses.Complete Payment') }}
                    </button>
                @endif
                
                <a href="{{ route('individual.dashboard') }}" 
                   class="btn btn-secondary">
                    {{ __('licenses.Back to Dashboard') }}
                </a>
            </div>
        </div>

    </div>
</x-layout>