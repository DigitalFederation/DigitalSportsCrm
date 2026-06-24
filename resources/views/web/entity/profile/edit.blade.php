<x-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="border-b border-gray-200 pb-5 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold leading-tight text-gray-900">{{ __('Entity Profile Management') }}</h1>
                    <p class="mt-2 text-sm text-gray-500">
                        {{ __('Manage your organization\'s profile and public presence') }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('entity.dashboard') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <x-heroicon-o-arrow-left class="w-5 h-5 mr-2 -ml-1" />
                        {{ __('Back to Dashboard') }}
                    </a>
                    <button type="submit" form="entity-profile-form"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <x-heroicon-o-check class="w-5 h-5 mr-2 -ml-1" />
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Form -->
        <form id="entity-profile-form" action="{{ route('entity.profile.update', $entity->id) }}" method="POST"
            enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-12 gap-6">
                <!-- Left Column - Core Information -->
                <div class="col-span-12 lg:col-span-8 space-y-6">
                    <!-- Profile Information Card -->
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                                    <x-heroicon-o-user-circle class="w-6 h-6 mr-2 text-gray-500" />
                                    {{ __('Core Information') }}
                                </h2>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ __('Required Section') }}
                                </span>
                            </div>

                            <!-- Entity Logo -->
                            <div class="mb-6">
                                <div class="flex items-start space-x-6">
                                    <div class="flex-shrink-0 w-full sm:w-1/2">
                                        <div class="flex flex-col space-y-4">
                                            <div class="relative h-24 w-24 mx-auto">
                                                <img id="preview_image"
                                                    src="{{ $entity->hasMedia('profile') ? $entity->getFirstMediaUrl('profile') : 'https://ui-avatars.com/api/?name=' . urlencode($entity->name) . '&color=7F9CF5&background=EBF4FF' }}"
                                                    class="rounded-full h-24 w-24 object-cover">
                                            </div>

                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-gray-900">{{ __('Entity Logo') }}</h3>
                                        <p class="text-sm text-gray-500">
                                            {{ __('Upload a high-quality logo for your organization') }}</p>
                                        <p class="mt-1 text-xs text-gray-400">
                                            {{ __('Recommended: PNG or JPG, at least 400x400px') }}</p>
                                            <div class="w-full">
                                                <input
                                                    onchange="document.getElementById('preview_image').src = window.URL.createObjectURL(this.files[0])"
                                                    name="logo" id="logo" type="file"
                                                    class="relative m-0 block w-full min-w-0 flex-auto rounded border border-solid border-gray-300 bg-clip-padding px-3 py-1.5 text-base font-normal text-gray-700 transition duration-300 ease-in-out file:-mx-3 file:py-2 file:overflow-hidden file:rounded-none file:border-0 file:border-solid file:border-inherit file:bg-gray-100 file:px-3 file:text-gray-700 file:transition file:duration-150 file:ease-in-out file:[margin-inline-end:0.75rem] file:[border-inline-end-width:1px] hover:file:bg-gray-200 focus:border-blue-500 focus:text-gray-700 focus:shadow-[0_0_0_1px] focus:shadow-blue-500 focus:outline-none"
                                                    accept="image/jpeg,image/png">
                                                <div class="text-xs mt-1 text-gray-500">
                                                    {{ __('*Only JPG or PNG files.') }}</div>
                                            </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Entity Names -->
                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">
                                        {{ __('Display Name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" name="name" id="name"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('name') ? 'border-red-300' : '' }}"
                                            value="{{ old('name', $entity->name) }}" required>
                                    </div>
                                    @if ($errors->has('name'))
                                        <p class="mt-1 text-sm text-red-600">{{ $errors->first('name') }}</p>
                                    @else
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ __('The name displayed across the platform') }}</p>
                                    @endif
                                </div>

                                <div>
                                    <label for="legal-name" class="block text-sm font-medium text-gray-700">
                                        {{ __('Legal Name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" name="legal_name" id="legal-name"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('legal_name') ? 'border-red-300' : '' }}"
                                            value="{{ old('legal_name', $entity->legal_name) }}" required>
                                    </div>
                                    @if ($errors->has('legal_name'))
                                        <p class="mt-1 text-sm text-red-600">{{ $errors->first('legal_name') }}</p>
                                    @else
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ __('Official registered name of your organization') }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Legal Information -->
                            <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                                <div>
                                    <label for="legal_responsible_person"
                                        class="block text-sm font-medium text-gray-700">
                                        {{ __('Legal Representative') }}
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" name="legal_responsible_person"
                                            id="legal_responsible_person"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('legal_responsible_person') ? 'border-red-300' : '' }}"
                                            value="{{ old('legal_responsible_person', $entity->legal_responsible_person) }}">
                                    </div>
                                    @if ($errors->has('legal_responsible_person'))
                                        <p class="mt-1 text-sm text-red-600">
                                            {{ $errors->first('legal_responsible_person') }}</p>
                                    @endif
                                </div>

                                <div>
                                    <label for="vat_number" class="block text-sm font-medium text-gray-700">
                                        {{ __('Tax ID / VAT Number') }}
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" name="vat_number" id="vat_number"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('vat_number') ? 'border-red-300' : '' }}"
                                            value="{{ old('vat_number', $entity->vat_number) }}">
                                    </div>
                                    @if ($errors->has('vat_number'))
                                        <p class="mt-1 text-sm text-red-600">{{ $errors->first('vat_number') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Information Card -->
                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                                    <x-heroicon-o-map-pin class="w-6 h-6 mr-2 text-gray-500" />
                                    {{ __('Location & Contact') }}
                                </h2>
                            </div>

                            <!-- Address Information -->
                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-4">
                                    <label for="address"
                                        class="block text-sm font-medium text-gray-700">{{ __('Street Address') }}</label>
                                    <div class="mt-1">
                                        <input type="text" name="address" id="address"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('address') ? 'border-red-300' : '' }}"
                                            value="{{ old('address', $entity->address) }}">
                                    </div>
                                    @if ($errors->has('address'))
                                        <p class="mt-1 text-sm text-red-600">{{ $errors->first('address') }}</p>
                                    @endif
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="location"
                                        class="block text-sm font-medium text-gray-700">{{ __('City/Town') }}</label>
                                    <div class="mt-1">
                                        <input type="text" name="location" id="location"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('location') ? 'border-red-300' : '' }}"
                                            value="{{ old('location', $entity->location) }}">
                                    </div>
                                    @if ($errors->has('location'))
                                        <p class="mt-1 text-sm text-red-600">{{ $errors->first('location') }}</p>
                                    @endif
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="country" class="block text-sm font-medium text-gray-700">
                                        {{ __('Country') }} <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1">
                                        <select id="country" name="country_id"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('country_id') ? 'border-red-300' : '' }}"
                                            required>
                                            <option value="">{{ __('Select a country') }}</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}"
                                                    {{ old('country_id', $entity->country_id) == $country->id ? 'selected' : '' }}>
                                                    {{ $country->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if ($errors->has('country_id'))
                                        <p class="mt-1 text-sm text-red-600">{{ $errors->first('country_id') }}</p>
                                    @endif
                                </div>

                                <div class="sm:col-span-3">

                                    <livewire:widgets.location-picker :initial-lat="old('lat', $entity->lat)" :initial-lng="old('lng', $entity->lng)"
                                        lat-field="lat" lng-field="lng" />
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-3">
                                <div>
                                    <label for="email"
                                        class="block text-sm font-medium text-gray-700">{{ __('Contact Email') }}</label>
                                    <div class="mt-1">
                                        <input type="email" name="email" id="email"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('email') ? 'border-red-300' : '' }}"
                                            value="{{ old('email', $entity->email) }}">
                                    </div>
                                    @if ($errors->has('email'))
                                        <p class="mt-1 text-sm text-red-600">{{ $errors->first('email') }}</p>
                                    @endif
                                </div>

                                <div>
                                    <label for="phone"
                                        class="block text-sm font-medium text-gray-700">{{ __('Phone Number') }}</label>
                                    <div class="mt-1">
                                        <input type="tel" name="phone" id="phone"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('phone') ? 'border-red-300' : '' }}"
                                            value="{{ old('phone', $entity->phone) }}">
                                    </div>
                                    @if ($errors->has('phone'))
                                        <p class="mt-1 text-sm text-red-600">{{ $errors->first('phone') }}</p>
                                    @endif
                                </div>

                                <div>
                                    <label for="website"
                                        class="block text-sm font-medium text-gray-700">{{ __('Website') }}</label>
                                    <div class="mt-1">
                                        <input type="url" name="website" id="website"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('website') ? 'border-red-300' : '' }}"
                                            value="{{ old('website', $entity->website) }}">
                                    </div>
                                    @if ($errors->has('website'))
                                        <p class="mt-1 text-sm text-red-600">{{ $errors->first('website') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Public Profile -->
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    <!-- Public Profile Preview Card -->
                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                                    <x-heroicon-o-globe-alt class="w-6 h-6 mr-2 text-gray-500" />
                                    {{ __('Public Profile') }}
                                </h2>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('Public View') }}
                                </span>
                            </div>

                            <!-- Background Image -->
                            @hasanyrole('entity-admin|entity-diving-services')
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('Profile Background') }}
                                    </label>
                                    <label for="entity_background"
                                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors cursor-pointer">
                                        <input id="entity_background" name="entity_background" type="file"
                                            class="sr-only" accept="image/*" onchange="previewBackgroundImage(this)">
                                        <div class="space-y-1 text-center">
                                            <x-heroicon-o-photo class="mx-auto h-12 w-12 text-gray-400" />
                                            <div class="flex text-sm text-gray-600 justify-center">
                                                <span
                                                    class="relative rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                    {{ __('Upload a file') }}
                                                </span>
                                                <p class="pl-1">{{ __('or drag and drop') }}</p>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                {{ __('PNG, JPG, GIF up to 2MB') }}
                                            </p>
                                        </div>
                                    </label>
                                    <div id="background_image_preview" class="mt-2 hidden">
                                        <div class="relative">
                                            <img id="preview_background_image" src="#" alt="{{ __('Background Preview') }}" class="rounded-lg shadow-sm w-full h-32 object-cover">
                                            <button type="button" onclick="removeBackgroundPreview()" class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1 shadow-sm hover:bg-red-700 focus:outline-none">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">{{ __('New image preview (not saved yet)') }}</p>
                                    </div>
                                    @if ($entity->getFirstMediaUrl('entity-background'))
                                        <div id="current_background_image" class="mt-2">
                                            <img src="{{ $entity->getFirstMediaUrl('entity-background', 'thumb') }}"
                                                alt="{{ __('Current Background') }}" class="rounded-lg shadow-sm w-full h-32 object-cover">
                                            <p class="text-xs text-gray-500 mt-1">{{ __('Current saved image') }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endhasanyrole

                            <!-- Public Description -->
                            @hasanyrole('entity-admin|entity-diving-services')
                                <div class="mb-6">
                                    <label for="public_description_editor" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('Public Description') }}
                                    </label>
                                    <div class="mt-1">
                                        <x-forms.tinymce-editor-static
                                            name="public_description"
                                            elementId="public_description_editor"
                                            value="{{ old('public_description', $entity->public_description) }}"
                                            class="{{ $errors->has('public_description') ? 'border-red-300' : '' }}"
                                            placeholder="{{ __('Describe your organization...') }}"
                                        />
                                    </div>
                                    @if ($errors->has('public_description'))
                                        <p class="mt-1 text-sm text-red-600">{{ $errors->first('public_description') }}
                                        </p>
                                    @else
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ __('This description will be displayed on your public profile page.') }}
                                        </p>
                                    @endif
                                </div>
                            @endhasanyrole

                        </div>
                    </div>

                    <!-- Preview Card -->
                    @hasanyrole('entity-admin|entity-diving-services')
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 shadow-sm rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-sm font-medium text-blue-900">{{ __('Profile Preview') }}</h3>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-200 text-blue-800">
                                        {{ __('Live') }}
                                    </span>
                                </div>
                                <p class="text-xs text-blue-700">
                                    {{ __('Changes will be reflected on your public profile after saving.') }}
                                </p>
                                <div class="mt-4">
                                    <a href="{{ route('public.entity.show', $entity) }}"
                                        class="inline-flex items-center text-sm text-blue-600 hover:text-blue-900">
                                        <x-heroicon-o-eye class="w-4 h-4 mr-1" />
                                        {{ __('View public profile') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endhasanyrole
                </div>
            </div>

            <!-- Form Actions -->
            <div
                class="flex justify-end space-x-4 sticky bottom-0 bg-white p-4 shadow-lg rounded-lg border border-gray-200">
                <a href="{{ route('entity.dashboard') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <x-heroicon-o-check class="w-5 h-5 mr-2 -ml-1" />
                    {{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>
</x-layout>

<script>
    function previewBackgroundImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                document.getElementById('preview_background_image').src = e.target.result;
                document.getElementById('background_image_preview').classList.remove('hidden');

                // Hide current image if it exists
                const currentImage = document.getElementById('current_background_image');
                if (currentImage) {
                    currentImage.classList.add('hidden');
                }
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeBackgroundPreview() {
        document.getElementById('entity_background').value = '';
        document.getElementById('background_image_preview').classList.add('hidden');

        // Show current image again if it exists
        const currentImage = document.getElementById('current_background_image');
        if (currentImage) {
            currentImage.classList.remove('hidden');
        }
    }
</script>
