<div class="overflow-hidden bg-white rounded-xl shadow-lg border border-gray-200">
    <!-- Header Section -->
    <div>
        <div class="bg-gradient-to-r from-primary to-primary-light px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/10 rounded-lg backdrop-blur-sm">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">{{ __('Digital ID Card') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="relative px-6 py-6">
        <div class="flex flex-col sm:flex-row gap-6">
            <!-- Left: Photo & QR -->
            <div class="flex flex-col items-center sm:items-start space-y-4">
                <!-- Profile Photo -->
                <div class="relative">
                    <div class="relative">
                        <x-secure-profile-image :individual="$individual" size="thumb" class="w-32 h-32 rounded-lg object-cover border-4 border-primary-light shadow-md" />
                        
                        <!-- Country Flag Overlay -->
                        <div class="absolute -bottom-2 -right-2 p-1 bg-white rounded-full shadow-md border-2 border-white">
                            <img class="w-8 h-8 rounded-full object-cover"
                                 src="{{ asset('img/flags/' . strtolower($individual->country->iso) . '.svg') }}"
                                 alt="{{ $individual->country->name }}">
                        </div>
                    </div>
                </div>
                
                <!-- QR Code -->
                @if(!empty($individualType) && in_array($individualType, ['individual', 'assistant', 'instructor']))
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <img src="{{ $individual->qrcode_path }}" 
                         alt="{{ $individual->member_code }}"
                         class="w-24 h-24">
                    <p class="text-xs text-center text-gray-500 mt-2">{{ __('Scan to verify') }}</p>
                </div>
                @endif
            </div>
            
            <!-- Right: Details -->
            <div class="flex-1 space-y-5">
                <!-- Name Section -->
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
                        {{ $individual->native_name }}
                    </h2>
                </div>
                
                <!-- Info Grid -->
                @if(!empty($individualType) && in_array($individualType, ['individual', 'assistant', 'instructor']))
                <div class="space-y-4">
                    <!-- Nº Filiado -->
                    <div class="bg-primary/5 rounded-lg p-4 border border-primary/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('main.Member Code') }}</p>
                                <p class="text-xl font-bold text-primary mt-1">{{ $individual->member_code }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Other Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if($individual->member_number)
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.member_number') }}</p>
                            <p class="text-lg font-semibold text-gray-900 mt-1">{{ $individual->member_number }}</p>
                        </div>
                        @endif
                        
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Nationality') }}</p>
                            <div class="flex items-center mt-1">
                                <img class="w-5 h-5 rounded-full mr-2" 
                                     src="{{ asset('img/flags/' . strtolower($individual->country->iso) . '.svg') }}"
                                     alt="{{ $individual->country->name }}">
                                <p class="text-lg font-semibold text-gray-900">{{ $individual->country->name }}</p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Birthday') }}</p>
                            <p class="text-lg font-semibold text-gray-900 mt-1">
                                {{ $individual->birthdate ? Carbon\Carbon::parse($individual->birthdate)->format('d/m/Y') : '---' }}
                            </p>
                        </div>
                        
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
</div>