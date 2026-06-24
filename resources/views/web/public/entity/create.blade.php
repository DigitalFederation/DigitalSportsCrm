@section('title', 'Entity account registration')
<x-public-layout>
    <main class="relative h-screen">

        <section
            class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="pt-10">
                <x-authentication-card-logo />
            </div>
            <div class="w-full md:w-4/5 lg:w-3/4 md:mx-auto">

                <div class="px-4 sm:px-6 lg:px-8 py-8 w-full mx-auto">
                    <!-- Page header -->
                    <div class="mb-8 text-center">
                        <h1 class="text-2xl md:text-3xl text-slate-600 font-bold">{{ __('Registo de Entidade') }}</h1>
                    </div>

                    @include('components.layout.banner_message')

                    <form class="w-full"
                          action="{{ route('entity.registration.submit') }}"
                          method="POST"
                          enctype="multipart/form-data"
                          x-data="{ 
                              validateEntityTypes() {
                                  const checkboxes = document.querySelectorAll('input[name=\'entity_types[]\']');
                                  const isChecked = Array.from(checkboxes).some(cb => cb.checked);
                                  if (!isChecked) {
                                      alert('{{ __('Por favor, selecione pelo menos um tipo de atividade.') }}');
                                      return false;
                                  }
                                  return true;
                              }
                          }"
                          @submit.prevent="validateEntityTypes() && $el.submit()">
                        @csrf
                        <x-honeypot />

                        <div class="mb-8 w-full">

                            <div class="bg-white shadow-lg rounded-lg flex flex-col md:flex-row md:-mr-px">
                                <div class="grow">
                                    <!-- Panel body -->
                                    <div class="p-6 space-y-6">
                                        <!-- Entity Information Section -->
                                        <section>
                                            <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('Informação da Entidade') }}</h3>

                                            <!-- Entity Type -->
                                            <div class="mt-5" x-data="{ entityTypes: {{ json_encode(old('entity_types', [])) }}, hasError: false }">
                                                <label class="block text-sm font-medium mb-1">
                                                    {{ __('Tipo de Atividade') }} <span class="text-rose-500">*</span>
                                                </label>
                                                <div class="space-y-2">
                                                    <label class="flex items-center">
                                                        <input type="checkbox" 
                                                               name="entity_types[]" 
                                                               value="sport" 
                                                               class="form-checkbox"
                                                               x-model="entityTypes"
                                                               @change="hasError = entityTypes.length === 0"
                                                               {{ in_array('sport', old('entity_types', [])) ? 'checked' : '' }}>
                                                        <span class="ml-2">{{ __('Desporto') }}</span>
                                                    </label>
                                                    <label class="flex items-center">
                                                        <input type="checkbox" 
                                                               name="entity_types[]" 
                                                               value="diving" 
                                                               class="form-checkbox"
                                                               x-model="entityTypes"
                                                               @change="hasError = entityTypes.length === 0"
                                                               {{ in_array('diving', old('entity_types', [])) ? 'checked' : '' }}>
                                                        <span class="ml-2">{{ __('Mergulho Recreativo, Técnico ou Científico') }}</span>
                                                    </label>
                                                </div>
                                                <div x-show="hasError" class="text-xs mt-1 text-rose-500">
                                                    {{ __('Selecione pelo menos um tipo de atividade') }}
                                                </div>
                                                @if($errors->has('entity_types'))
                                                    <div class="text-xs mt-1 text-rose-500">{{ $errors->first('entity_types') }}</div>
                                                @endif
                                            </div>

                                            <!-- Names Section -->
                                            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                                <div class="sm:w-1/2">
                                                    <label class="block text-sm font-medium mb-1" for="name">
                                                        {{ __('Nome da Entidade') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input id="name"
                                                           class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                                                           type="text"
                                                           name="name"
                                                           x-model="name"
                                                           x-on:input="$refs.legalName.value = name"
                                                           value="{{ old('name') }}"
                                                           required />
                                                    @if($errors->has('name'))
                                                        <div class="text-xs mt-1 text-rose-500">{{ $errors->first('name') }}</div>
                                                    @endif
                                                </div>

                                                <div class="sm:w-1/2">
                                                    <label class="block text-sm font-medium mb-1" for="legal-name">
                                                        {{ __('Nome de Registo Fiscal') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input id="legal-name"
                                                           x-ref="legalName"
                                                           class="form-input w-full {{ $errors->has('legal_name') ? 'border-rose-300' : '' }}"
                                                           type="text"
                                                           name="legal_name"
                                                           value="{{ old('legal_name') }}"
                                                           required />
                                                    @if($errors->has('legal_name'))
                                                        <div class="text-xs mt-1 text-rose-500">{{ $errors->first('legal_name') }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Organization Details -->
                                            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                                <div class="sm:w-1/2">
                                                    <label class="block text-sm font-medium mb-1" for="legal_responsible_person">
                                                        {{ __('Responsável Legal') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input id="legal_responsible_person"
                                                           class="form-input w-full {{ $errors->has('legal_responsible_person') ? 'border-rose-300' : '' }}"
                                                           type="text" name="legal_responsible_person"
                                                           value="{{ old('legal_responsible_person') }}"
                                                           required />
                                                    @if($errors->has('legal_responsible_person'))
                                                        <div class="text-xs mt-1 text-rose-500">
                                                            {{ $errors->first('legal_responsible_person') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="sm:w-1/2">
                                                    <label class="block text-sm font-medium mb-1" for="vat_number">
                                                        {{ __('NIF') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input type="text" id="vat_number" name="vat_number"
                                                           class="form-input w-full {{ $errors->has('vat_number') ? 'border-rose-300' : '' }}"
                                                           value="{{ old('vat_number') }}"
                                                           required>
                                                    @if($errors->has('vat_number'))
                                                        <div class="text-xs mt-1 text-rose-500">
                                                            {{ $errors->first('vat_number') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Logo Upload -->
                                            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                                <div class="sm:w-1/2" x-data="{ logoError: '' }">
                                                    <label class="block text-sm font-medium mb-1" for="logo">
                                                        {{ __('Logo da Entidade') }}
                                                    </label>
                                                    <input
                                                        name="logo"
                                                        class="form-input w-full"
                                                        type="file"
                                                        accept=".jpeg,.jpg,.png,.gif"
                                                        @change="
                                                            logoError = '';
                                                            if ($event.target.files.length) {
                                                                const file = $event.target.files[0];
                                                                const maxSize = 5 * 1024 * 1024;
                                                                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                                                                if (!allowedTypes.includes(file.type)) {
                                                                    logoError = '{{ __('entity_registration.logo_invalid_type') }}';
                                                                    $event.target.value = '';
                                                                } else if (file.size > maxSize) {
                                                                    logoError = '{{ __('entity_registration.logo_too_large') }}';
                                                                    $event.target.value = '';
                                                                } else {
                                                                    document.getElementById('preview_image')?.setAttribute('src', URL.createObjectURL(file));
                                                                }
                                                            }
                                                        " />
                                                    <div class="text-xs mt-1">{{ __('entity_registration.logo_hint') }}</div>
                                                    <div x-show="logoError" x-text="logoError" class="text-xs mt-1 text-rose-500" x-cloak></div>
                                                    @if($errors->has('logo'))
                                                        <div class="text-xs mt-1 text-rose-500">
                                                            {{ $errors->first('logo') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>



                                        </section>


                                        <!-- Location Section -->
                                        <section>
                                            <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('Localização da Sede') }}</h3>

                                            <!-- District -->
                                            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                                <div class="sm:w-1/2">
                                                    <label class="block text-sm font-medium mb-1" for="district_id">
                                                        {{ __('Distrito') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <select id="district_id"
                                                            name="district_id"
                                                            class="form-select w-full {{ $errors->has('district_id') ? 'border-rose-300' : '' }}"
                                                            required>
                                                        <option value="">{{ __('Selecione o distrito') }}</option>
                                                        @foreach(\Domain\Geographic\Models\District::where('is_active', true)->orderBy('name')->get() as $district)
                                                            <option value="{{ $district->id }}" {{ old('district_id') == $district->id ? 'selected' : '' }}>
                                                                {{ $district->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @if($errors->has('district_id'))
                                                        <div class="text-xs mt-1 text-rose-500">{{ $errors->first('district_id') }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Address and Postal Code -->
                                            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                                <div class="sm:w-3/6">
                                                    <label class="block text-sm font-medium mb-1" for="address">
                                                        {{ __('Morada') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input id="address"
                                                           class="form-input w-full {{ $errors->has('address') ? 'border-rose-300' : '' }}"
                                                           type="text"
                                                           name="address"
                                                           value="{{ old('address') }}"
                                                           required />
                                                    @if($errors->has('address'))
                                                        <div class="text-xs mt-1 text-rose-500">{{ $errors->first('address') }}</div>
                                                    @endif
                                                </div>

                                                <div class="sm:w-2/6">
                                                    <label class="block text-sm font-medium mb-1" for="location">
                                                        {{ __('Localidade') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input id="location"
                                                           class="form-input w-full {{ $errors->has('location') ? 'border-rose-300' : '' }}"
                                                           type="text"
                                                           name="location"
                                                           value="{{ old('location') }}"
                                                           required />
                                                    @if($errors->has('location'))
                                                        <div class="text-xs mt-1 text-rose-500">{{ $errors->first('location') }}</div>
                                                    @endif
                                                </div>

                                                <div class="sm:w-1/6">
                                                    <label class="block text-sm font-medium mb-1" for="postal_code">
                                                        {{ __('Código Postal') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input id="postal_code"
                                                           class="form-input w-full {{ $errors->has('postal_code') ? 'border-rose-300' : '' }}"
                                                           type="text"
                                                           name="postal_code"
                                                           value="{{ old('postal_code') }}"
                                                           required />
                                                    @if($errors->has('postal_code'))
                                                        <div class="text-xs mt-1 text-rose-500">{{ $errors->first('postal_code') }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- GPS Coordinates with Map Picker -->
                                            <div class="mt-5">
                                                <label class="block text-sm font-medium mb-1">
                                                    {{ __('Localização (GPS)') }}
                                                </label>
                                                <p class="text-xs text-gray-600 mb-2">{{ __('Clique no botão abaixo para selecionar a localização no mapa') }}</p>
                                                <livewire:widgets.location-picker 
                                                    :initial-lat="old('lat')" 
                                                    :initial-lng="old('lng')"
                                                    lat-field="lat" 
                                                    lng-field="lng" />
                                            </div>
                                        </section>

                                        <!-- Contact Information Section -->
                                        <section>
                                            <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('Contactos Públicos') }}</h3>

                                            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                                <div class="sm:w-1/3">
                                                    <label class="block text-sm font-medium mb-1" for="email">
                                                        {{ __('Email de Contacto') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input id="email"
                                                           class="form-input w-full {{ $errors->has('email') ? 'border-rose-300' : '' }}"
                                                           type="email"
                                                           name="email"
                                                           value="{{ old('email') }}"
                                                           required />
                                                    @if($errors->has('email'))
                                                        <div class="text-xs mt-1 text-rose-500">{{ $errors->first('email') }}</div>
                                                    @endif
                                                </div>

                                                <div class="sm:w-1/3">
                                                    <label class="block text-sm font-medium mb-1" for="phone_number">
                                                        {{ __('Telefone') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input id="phone_number"
                                                           class="form-input w-full {{ $errors->has('phone') ? 'border-rose-300' : '' }}"
                                                           type="tel"
                                                           name="phone"
                                                           value="{{ old('phone') }}"
                                                           required />
                                                    @if($errors->has('phone'))
                                                        <div class="text-xs mt-1 text-rose-500">{{ $errors->first('phone') }}</div>
                                                    @endif
                                                </div>

                                                <div class="sm:w-1/3">
                                                    <label class="block text-sm font-medium mb-1" for="website">
                                                        {{ __('Website') }}
                                                    </label>
                                                    <input id="website"
                                                           class="form-input w-full {{ $errors->has('website') ? 'border-rose-300' : '' }}"
                                                           type="text"
                                                           name="website"
                                                           value="{{ old('website') }}" />
                                                    @if($errors->has('website'))
                                                        <div class="text-xs mt-1 text-rose-500">{{ $errors->first('website') }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </section>

                                        <section>
                                            <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('Informação de Login') }}</h3>
                                            <p class="text-gray-500 text-sm mb-4">{{ __('Crie credenciais para aceder à plataforma.') }}</p>

                                            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                                <div class="sm:w-1/3">
                                                    <label class="block text-sm font-medium mb-1" for="user_email">
                                                        {{ __('Email de Login') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input id="user_email"
                                                           class="form-input w-full {{ $errors->has('user_email') ? 'border-rose-300' : '' }}"
                                                           type="text" name="user_email"
                                                           value="{{ old('user_email') }}" />
                                                    @if($errors->has('user_email'))
                                                        <div class="text-xs mt-1 text-rose-500">
                                                            {{ $errors->first('user_email') }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="sm:w-1/3">
                                                    <label class="block text-sm font-medium mb-1" for="password">
                                                        {{ __('Password') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input id="password"
                                                           class="form-input w-full {{ $errors->has('password') ? 'border-rose-300' : '' }}"
                                                           type="password" name="password" />
                                                    @if($errors->has('password'))
                                                        <div class="text-xs mt-1 text-rose-500">
                                                            {{ $errors->first('password') }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="sm:w-1/3">
                                                    <label class="block text-sm font-medium mb-1" for="user_password_confirmation">
                                                        {{ __('Confirmar Password') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input id="user_password_confirmation"
                                                           class="form-input w-full {{ $errors->has('user_password_confirmation') ? 'border-rose-300' : '' }}"
                                                           type="password" name="password_confirmation" />
                                                    @if($errors->has('user_password_confirmation'))
                                                        <div class="text-xs mt-1 text-rose-500">
                                                            {{ $errors->first('user_password_confirmation') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                        </section>

                                        <!-- Terms and Privacy Section -->
                                        <section class="border-t border-slate-200">
                                            <!-- Terms of Service -->
                                            <div class="flex flex-col mt-4">
                                                <label class="flex items-start text-sm font-medium mb-1">
                                                    <input id="terms" class="mr-2 mt-1" type="checkbox" name="terms" value="1" required>
                                                    <span>
                                                        {{ __('Declaro que li e concordo com os ') }}
                                                        <a href="{{ route('terms-of-service') }}" target="_blank" class="text-indigo-500 hover:text-indigo-600">{{ __('Termos de Serviço') }}</a>
                                                        {{ __(' e com a ') }}
                                                        <a href="{{ route('privacy-policy') }}" target="_blank" class="text-indigo-500 hover:text-indigo-600">{{ __('Política de Privacidade') }}</a>.
                                                    </span>
                                                    <span class="text-rose-500">*</span>
                                                </label>
                                                @if($errors->has('terms'))
                                                    <div class="text-xs mt-1 text-rose-500">
                                                        {{ $errors->first('terms') }}
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Data Sharing Policy -->
                                            <div class="flex flex-col mt-2">
                                                <label class="flex items-start text-sm font-medium mb-1">
                                                    <input id="dataSharing" class="mr-2 mt-1" type="checkbox" name="data_sharing" value="1" required>
                                                    <span>
                                                        {{ __('Autorizo a partilha dos meus dados com terceiros autorizados para os fins descritos na ') }}
                                                        <a href="{{ route('data-sharing-policy') }}" target="_blank" class="text-indigo-500 hover:text-indigo-600">{{ __('Política de Partilha de Dados') }}</a>.
                                                    </span>
                                                    <span class="text-rose-500">*</span>
                                                </label>
                                                @if($errors->has('data_sharing'))
                                                    <div class="text-xs mt-1 text-rose-500">
                                                        {{ $errors->first('data_sharing') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </section>

                                        <!-- Footer -->
                                        <footer>
                                            <div class="flex flex-col px-6 py-5 border-t border-slate-200">
                                                <div class="flex self-end">
                                                    <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white ml-3">
                                                        {{ __('Registar Entidade') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </footer>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </section>
    </main>
</x-public-layout>
