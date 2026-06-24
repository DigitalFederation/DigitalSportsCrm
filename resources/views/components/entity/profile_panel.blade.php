<section class="w-full overflow-hidden rounded-xl bg-white shadow-lg border border-secondary/30">
    <!-- Header with gradient background -->
    <div class="relative bg-gradient-to-r from-primary to-primary-light p-4 text-white">
        <div class="flex justify-between items-center">
            <h3 class="text-xl font-bold tracking-tight">{{ __('Organization Profile') }}</h3>
            <div class="flex items-center space-x-1">
                <span class="text-xs font-medium bg-white/20 py-1 px-2 rounded">{{ $entity->type }}</span>
            </div>
        </div>
    </div>
    
    <!-- Main content -->
    <div class="p-5">
        <div class="flex flex-col lg:flex-row gap-5">
            <!-- Left column with logo and QR code -->
            <div class="flex flex-col items-center space-y-3">
                <!-- Entity logo with border -->
                <div class="relative">
                    <a href="#" target="_blank" class="block">
                        <div class="w-32 h-32 rounded-lg overflow-hidden border-4 border-primary-light shadow-md">
                            <img class="object-cover w-full h-full"
                                src="{{ $entity->getFirstMediaUrl('profile', 'thumb') ?: asset('img/user_placeholder.png') }}"
                                alt="{{ $entity->name }}">
                        </div>
                    </a>
                    
                    <!-- Country flag badge -->
                    <div class="absolute -bottom-2 -right-2 rounded-full border-2 border-white bg-white p-1 shadow-md">
                        <img class="w-8 h-8 rounded-full object-cover"
                            src="{{ asset('img/flags/' . strtolower($entity->country->iso) . '.svg') }}"
                            alt="{{ $entity->country->name }} Flag">
                    </div>
                </div>
                
                <!-- QR Code -->
                <div class="mt-2 p-2 bg-white rounded-lg border border-secondary shadow-sm">
                    <img src="{{ $entity->qrcode_path }}" alt="{{ $entity->member_code }}"
                        class="w-24 h-24 object-cover">
                    <p class="text-xs text-center text-gray-500 mt-1">{{ __('Scan to verify') }}</p>
                </div>
            </div>
            
            <!-- Right column with organization details -->
            <div class="flex-grow">
                <!-- Organization name with link -->
                <div class="mb-4 pb-3 border-b border-secondary/30">
                    <a href="#" target="_blank"
                        class="group">
                        <h2 class="sm:text-lg md:text-2xl font-bold text-gray-800 group-hover:text-primary transition-colors">
                            {{ $entity->name }}
                        </h2>
                    </a>
                </div>
                
                <!-- Organization information grid -->
                <div class="grid grid-cols-1 gap-4">
                    <!-- Nº Filiado -->
                    <div class="bg-primary/5 p-3 rounded-lg">
                        <div class="flex justify-between items-center">
                            <p class="text-sm font-medium text-gray-500">{{ __('main.Member Code') }}</p>
                            <p class="text-lg font-bold text-primary">{{ $entity->member_code }}</p>
                        </div>
                    </div>
                    
                    <!-- Member Number -->
                    @if($entity->member_number)
                    <div class="flex justify-between items-center border-b border-secondary/20 pb-2">
                        <p class="text-sm font-medium text-gray-500">{{ __('main.member_number') }}</p>
                        <p class="text-base font-semibold text-gray-800">{{ $entity->member_number }}</p>
                    </div>
                    @endif
                    
                    <!-- Country -->
                    <div class="flex justify-between items-center border-b border-secondary/20 pb-2">
                        <p class="text-sm font-medium text-gray-500">{{ __('Country') }}</p>
                        <div class="flex items-center">
                            <p class="text-base font-medium text-gray-800">{{ $entity->country->name }}</p>
                        </div>
                    </div>
                    
                    <!-- Type -->
                    <div class="flex justify-between items-center border-b border-secondary/20 pb-2">
                        <p class="text-sm font-medium text-gray-500">{{ __('Type') }}</p>
                        <p class="text-base font-medium text-gray-800">{{ $entity->type }}</p>
                    </div>
                    
                    <!-- Status indicator -->
                    <div class="mt-2">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            <p class="text-sm text-gray-500">{{ __('Active Organization') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer with subtle branding -->
    <div class="bg-secondary/10 px-5 py-3 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <div class="w-5 h-5 bg-primary rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <span class="text-xs font-medium text-gray-500">{{ __('Verified Organization') }}</span>
        </div>
        <div>
            <p class="text-xs text-gray-400">ID: {{ substr($entity->id, 0, 8) }}</p>
        </div>
    </div>
</section>
