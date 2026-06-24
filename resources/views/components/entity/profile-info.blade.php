@props(['entity'])

<div class="bg-white rounded-lg border border-gray-200 p-6">
    <h3 class="font-semibold text-gray-900 mb-4">{{ __('entity.information') }}</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Entity Name -->
        <div>
            <dt class="text-sm font-medium text-gray-500">{{ __('entity.name') }}</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $entity->name }}</dd>
        </div>

        <!-- Legal Name -->
        @if($entity->legal_name)
        <div>
            <dt class="text-sm font-medium text-gray-500">{{ __('entity.legal_name') }}</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $entity->legal_name }}</dd>
        </div>
        @endif

        <!-- Member Code -->
        <div>
            <dt class="text-sm font-medium text-gray-500">{{ __('main.Member Code') }}</dt>
            <dd class="mt-1 text-sm font-semibold text-primary">{{ $entity->member_code }}</dd>
        </div>

        <!-- Member Number -->
        @if($entity->member_number)
        <div>
            <dt class="text-sm font-medium text-gray-500">{{ __('main.member_number') }}</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $entity->member_number }}</dd>
        </div>
        @endif

        <!-- VAT Number -->
        @if($entity->vat_number)
        <div>
            <dt class="text-sm font-medium text-gray-500">{{ __('entity.tax_identification_number') }}</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $entity->vat_number }}</dd>
        </div>
        @endif

        <!-- Country -->
        <div>
            <dt class="text-sm font-medium text-gray-500">{{ __('entity.country') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 flex items-center gap-2">
                <img class="w-4 h-4 rounded-full"
                     src="{{ asset('img/flags/' . strtolower($entity->country->iso) . '.svg') }}"
                     alt="{{ $entity->country->name }}">
                {{ $entity->country->name }}
            </dd>
        </div>

        <!-- Address -->
        @if($entity->address)
        <div>
            <dt class="text-sm font-medium text-gray-500">{{ __('entity.address') }}</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $entity->address }}</dd>
        </div>
        @endif

        <!-- Location & Postal Code -->
        @if($entity->location || $entity->postal_code)
        <div>
            <dt class="text-sm font-medium text-gray-500">{{ __('entity.hq_address_city_postal') }}</dt>
            <dd class="mt-1 text-sm text-gray-900">
                {{ $entity->location }}{{ $entity->postal_code ? ', ' . $entity->postal_code : '' }}
            </dd>
        </div>
        @endif

        <!-- Legal Responsible Person -->
        @if($entity->legal_responsible_person)
        <div>
            <dt class="text-sm font-medium text-gray-500">{{ __('entity.responsible_person_name') }}</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $entity->legal_responsible_person }}</dd>
        </div>
        @endif
    </div>

    <!-- Contact Information Section -->
    <div class="mt-6 pt-6 border-t border-gray-200">
        <h4 class="font-medium text-gray-900 mb-4">{{ __('entity.public_contacts') }}</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Email -->
            @if($entity->email)
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('entity.contact_email') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    <a href="mailto:{{ $entity->email }}" class="text-primary hover:underline">{{ $entity->email }}</a>
                </dd>
            </div>
            @endif

            <!-- Phone -->
            @if($entity->phone)
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('entity.phone_number') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $entity->phone }}</dd>
            </div>
            @endif

            <!-- Website -->
            @if($entity->website)
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('entity.website') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    <a href="{{ $entity->website }}" target="_blank" class="text-primary hover:underline flex items-center gap-1">
                        {{ $entity->website }}
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </dd>
            </div>
            @endif
        </div>
    </div>

    <!-- Social Media Section -->
    @if($entity->facebook_url || $entity->instagram_url || $entity->linkedin_url || $entity->x_url)
    <div class="mt-6 pt-6 border-t border-gray-200">
        <h4 class="font-medium text-gray-900 mb-4">{{ __('entity.social_media_links') }}</h4>
        <div class="flex flex-wrap gap-3">
            @if($entity->facebook_url)
            <a href="{{ $entity->facebook_url }}" target="_blank"
               class="inline-flex items-center gap-2 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                Facebook
            </a>
            @endif

            @if($entity->instagram_url)
            <a href="{{ $entity->instagram_url }}" target="_blank"
               class="inline-flex items-center gap-2 px-3 py-2 bg-pink-50 text-pink-700 rounded-lg hover:bg-pink-100 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
                Instagram
            </a>
            @endif

            @if($entity->linkedin_url)
            <a href="{{ $entity->linkedin_url }}" target="_blank"
               class="inline-flex items-center gap-2 px-3 py-2 bg-blue-50 text-blue-800 rounded-lg hover:bg-blue-100 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                </svg>
                LinkedIn
            </a>
            @endif

            @if($entity->x_url)
            <a href="{{ $entity->x_url }}" target="_blank"
               class="inline-flex items-center gap-2 px-3 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
                X
            </a>
            @endif
        </div>
    </div>
    @endif
</div>
