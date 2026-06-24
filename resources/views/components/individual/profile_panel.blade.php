<section class="w-full overflow-hidden rounded-xl bg-white shadow-lg border border-secondary/30">
    <!-- Header with gradient background -->
    <div class="relative bg-gradient-to-r from-primary to-primary-light p-4 text-white">
        <div class="flex justify-between items-center">
            <h3 class="text-xl font-bold tracking-tight">{{ __('Digital ID Card') }}</h3>
        </div>
    </div>
    
    <!-- Main content -->
    <div class="p-5">
        <div class="flex flex-col md:flex-row gap-5">
            <!-- Left column with photo and QR code -->
            <div class="flex flex-col items-center space-y-3">
                <!-- Profile photo with border -->
                <div class="relative">
                    <a href="#" class="block">
                        <div class="w-32 h-32 rounded-lg overflow-hidden border-4 border-primary-light shadow-md">
                            <x-secure-profile-image :individual="$individual" size="thumb" class="object-cover w-full h-full" />
                        </div>
                    </a>
                    
                    <!-- Country flag badge -->
                    <div class="absolute -bottom-2 -right-2 rounded-full border-2 border-white bg-white p-1 shadow-md">
                        <img class="w-8 h-8 rounded-full object-cover"
                            src="{{ asset('img/flags/' . strtolower($individual->country->iso) . '.svg') }}"
                            alt="{{ $individual->country->name }} Flag">
                    </div>
                </div>
                
                <!-- QR Code -->
                @if(!empty($individualType) && in_array($individualType, ['individual', 'assistant', 'instructor']))
                <div class="mt-2 p-2 bg-white rounded-lg border border-secondary shadow-sm">
                    <img src="{{ $individual->qrcode_path }}" alt="{{ $individual->member_code }}"
                        class="w-24 h-24 object-cover">
                    <p class="text-xs text-center text-gray-500 mt-1">{{ __('Scan to verify') }}</p>
                </div>
                @endif
            </div>
            
            <!-- Right column with personal details -->
            <div class="flex-grow">
                <!-- Name with link -->
                <div class="mb-4 pb-3 border-b border-secondary/30">
                    <a href="#"
                        class="group">
                        <h2 class="text-2xl font-bold text-gray-800 group-hover:text-primary transition-colors">
                            {{ $individual->native_name }}
                        </h2>
                    </a>
                </div>
                
                <!-- Personal information grid -->
                @if(!empty($individualType) && in_array($individualType, ['individual', 'assistant', 'instructor']))
                <div class="grid grid-cols-1 gap-4">
                    <!-- Nº Filiado -->
                    <div class="bg-primary/5 p-3 rounded-lg">
                        <div class="flex justify-between items-center">
                            <p class="text-sm font-medium text-gray-500">{{ __('main.Member Code') }}</p>
                            <p class="text-lg font-bold text-primary">{{ $individual->member_code }}</p>
                        </div>
                    </div>
                    
                    <!-- Member Number -->
                    @if($individual->member_number)
                    <div class="flex justify-between items-center border-b border-secondary/20 pb-2">
                        <p class="text-sm font-medium text-gray-500">{{ __('main.member_number') }}</p>
                        <p class="text-base font-semibold text-gray-800">{{ $individual->member_number }}</p>
                    </div>
                    @endif
                    
                    <!-- Nationality -->
                    <div class="flex justify-between items-center border-b border-secondary/20 pb-2">
                        <p class="text-sm font-medium text-gray-500">{{ __('Nationality') }}</p>
                        <div class="flex items-center">
                            <p class="text-base font-medium text-gray-800">{{ $individual->country->name }}</p>
                        </div>
                    </div>
                    
                    <!-- Birthday -->
                    <div class="flex justify-between items-center border-b border-secondary/20 pb-2">
                        <p class="text-sm font-medium text-gray-500">{{ __('Birthday') }}</p>
                        <p class="text-base font-medium text-gray-800">{{ $individual->birthdate ? Carbon\Carbon::parse($individual->birthdate)->format('d/m/Y') : '---' }}</p>
                    </div>
                    
                </div>
                @endif
            </div>
        </div>
    </div>
    
</section>
