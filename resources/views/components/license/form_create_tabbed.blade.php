<div x-data="licenseFormTabs()" class="sm:flex sm:space-x-4">
    <div class="mb-8 sm:w-full">
        <div class="bg-white shadow-lg rounded-sm flex flex-col md:flex-row md:-mr-px">
            <div class="grow">
                <!-- Tab Navigation -->
                <div class="border-b border-slate-200">
                    <nav class="flex flex-wrap -mb-px">
                        <button type="button" @click="activeTab = 'basic'"
                                :class="activeTab === 'basic' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-slate-600 hover:text-slate-800'"
                                class="px-6 py-3 text-sm font-medium transition-colors">
                            {{ __('licenses.basic_information') }}
                        </button>
                        <button type="button" @click="activeTab = 'roles'"
                                :class="activeTab === 'roles' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-slate-600 hover:text-slate-800'"
                                class="px-6 py-3 text-sm font-medium transition-colors">
                            {{ __('licenses.roles_permissions') }}
                        </button>
                        <button type="button" @click="activeTab = 'requirements'"
                                :class="activeTab === 'requirements' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-slate-600 hover:text-slate-800'"
                                class="px-6 py-3 text-sm font-medium transition-colors">
                            {{ __('licenses.requirements') }}
                        </button>
                        <button type="button" @click="activeTab = 'pricing'"
                                :class="activeTab === 'pricing' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-slate-600 hover:text-slate-800'"
                                class="px-6 py-3 text-sm font-medium transition-colors">
                            {{ __('licenses.pricing') }}
                        </button>
                        <button type="button" @click="activeTab = 'availability'"
                                :class="activeTab === 'availability' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-slate-600 hover:text-slate-800'"
                                class="px-6 py-3 text-sm font-medium transition-colors">
                            {{ __('licenses.availability') }}
                        </button>
                        <button type="button" @click="activeTab = 'advanced'"
                                :class="activeTab === 'advanced' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-slate-600 hover:text-slate-800'"
                                class="px-6 py-3 text-sm font-medium transition-colors">
                            {{ __('licenses.advanced_settings') }}
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Basic Information Tab -->
                    <div x-show="activeTab === 'basic'" x-transition>
                        <div class="space-y-6">
                            <div class="card">
                                <h3 class="text-lg font-semibold mb-4">{{ __('licenses.basic_information') }}</h3>

                                <!-- Logo Upload -->
                                <div class="mb-6">
                                    <div class="flex items-start">
                                        @if($license->hasMedia('logo'))
                                            <div class="mr-4 flex-shrink-0">
                                                <label class="block text-sm font-medium mb-1">{{ __('Logo Atual') }}</label>
                                                <img src="{{ $license->getFirstMediaUrl('logo', 'thumb') }}"
                                                     alt="{{ $license->name ?: 'License' }} Logo"
                                                     class="w-16 h-16 object-contain rounded-lg shadow-md border border-slate-200">
                                            </div>
                                        @endif

                                        <div class="flex-grow">
                                            <label class="block text-sm font-medium mb-1" for="logo">
                                                {{ $license->hasMedia('logo') ? __('Carregar Novo Logo') : __('Imagem/Logo da Licença') }}
                                            </label>
                                            <input type="file" name="logo" id="logo" class="form-input w-full @error('logo') border-rose-300 @enderror">
                                            <div class="text-xs mt-1 text-slate-500">
                                                {{ __('Deixe em branco para manter o logo atual. Apenas ficheiros JPG ou PNG.') }}
                                            </div>
                                            @error('logo')
                                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Basic Fields -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="name">
                                            {{ __('Nome da Licença') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="text" name="name" id="name"
                                               class="form-input w-full @error('name') border-rose-300 @enderror"
                                               value="{{ old('name', $license->name) }}" required>
                                        @error('name')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="license_code">
                                            {{ __('Código da Licença') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="text" name="license_code" id="license_code"
                                               class="form-input w-full @error('license_code') border-rose-300 @enderror"
                                               value="{{ old('license_code', $license->license_code) }}" required>
                                        @error('license_code')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Committee and Type -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="committee_id">
                                            {{ __('Comité') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <select name="committee_id" id="committee_id"
                                                class="form-select w-full @error('committee_id') border-rose-300 @enderror"
                                                required>
                                            <option value="" disabled selected>{{ __('-- Selecione uma opção --') }}</option>
                                            @foreach($committees as $committee)
                                                <option value="{{ $committee->id }}"
                                                        @if(old('committee_id', $license->committee_id) == $committee->id) selected @endif>
                                                    {{ $committee->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('committee_id')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="license_type">
                                            {{ __('Tipo') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <select name="type_id" id="license_type"
                                                class="form-select w-full @error('type_id') border-rose-300 @enderror"
                                                @change="handleLicenseTypeChange()"
                                                required>
                                            <option value="" disabled selected>{{ __('-- Selecione uma opção --') }}</option>
                                            @foreach($licenseTypes as $type)
                                                <option value="{{ $type->id }}"
                                                        @if(old('type_id', $license->type_id) == $type->id) selected @endif>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="text-xs mt-1 text-slate-500">{{ __('entidade ou individual?') }}</div>
                                        @error('type_id')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Sport and Professional Role -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">{{ __('Desporto') }}</label>
                                        @php
                                            $selectedSports = old('sport_ids', $license->sports ? $license->sports->pluck('id')->toArray() : []);
                                        @endphp
                                        <div class="space-y-2 max-h-48 overflow-y-auto bg-slate-50 rounded-lg p-3 @error('sport_ids') border border-rose-300 @enderror">
                                            @foreach($sports as $sport)
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                           name="sport_ids[]"
                                                           value="{{ $sport->id }}"
                                                           class="form-checkbox h-4 w-4 text-blue-600"
                                                           @if(in_array($sport->id, $selectedSports)) checked @endif>
                                                    <span class="ml-2 text-sm">{{ $sport->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <div class="text-xs mt-1 text-slate-500">{{ __('(apenas se aplicável)') }}</div>
                                        @error('sport_ids')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                        @error('sport_ids.*')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="function_id">
                                            {{ __('Função') }}
                                        </label>
                                        <select name="function_id" id="function_id"
                                                class="form-select w-full @error('function_id') border-rose-300 @enderror"
                                                @change="handleProfessionalRoleChange()">
                                            <option value="">{{ __('-- Nenhuma --') }}</option>
                                            @foreach($professionalRoles as $role)
                                                <option value="{{ $role->id }}"
                                                        @if(old('function_id', $license->professional_role_id) == $role->id) selected @endif>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="text-xs mt-1 text-slate-500">{{ __('para que tipo é esta licença') }}</div>
                                        @error('function_id')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Requester Model -->
                                <div class="mt-6">
                                    <label class="block text-sm font-medium mb-2">
                                        {{ __('Requerente') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="bg-slate-50 rounded-lg p-4">
                                        <div class="space-y-2">
                                            @php
                                                $currentRequesterModel = old('requester_model', $license->requester_model ?? []);
                                                if (!is_array($currentRequesterModel)) {
                                                    $currentRequesterModel = [];
                                                }
                                                $currentRequesterModelLower = array_map('strtolower', $currentRequesterModel);
                                            @endphp
                                            @foreach($requesterModels as $requester => $val)
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                           name="requester_model[]"
                                                           value="{{ $val }}"
                                                           class="form-checkbox h-4 w-4 text-blue-600"
                                                           @change="handleRequesterChange()"
                                                           @if(in_array(strtolower($val), $currentRequesterModelLower)) checked @endif>
                                                    <span class="ml-2 text-sm">{{ $requester }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="text-xs mt-2 text-slate-500">
                                        {{ __('*Escolha quem pode solicitar esta licença') }}
                                    </div>
                                    @error('requester_model')
                                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Roles & Permissions Tab -->
                    <div x-show="activeTab === 'roles'" x-transition>
                        <div class="space-y-6">
                            <div class="card">
                                <h3 class="text-lg font-semibold mb-4">{{ __('licenses.roles_permissions') }}</h3>

                                <div class="information-box mb-4">
                                    <svg class="inline-block w-4 h-4 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ __('admin.role_mappings.license.select_roles_help') }}
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-3">
                                        {{ __('admin.role_mappings.roles') }}
                                    </label>

                                    @php
                                        $selectedRoles = old('roles', isset($license->id) ? $license->roles->pluck('id')->toArray() : []);
                                    @endphp

                                    <div class="bg-slate-50 rounded-lg p-4">
                                        @if(isset($roles) && $roles->count() > 0)
                                            <!-- Roles Grid -->
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                @foreach($roles as $role)
                                                    <label class="flex items-start">
                                                        <input type="checkbox"
                                                               name="roles[]"
                                                               value="{{ $role->id }}"
                                                               class="form-checkbox h-4 w-4 text-blue-600 mt-0.5 role-checkbox @error('roles') border-rose-300 @enderror"
                                                               @if(in_array($role->id, $selectedRoles)) checked @endif>
                                                        <div class="ml-2">
                                                            <span class="text-sm font-medium text-slate-700">{{ $role->name }}</span>
                                                            @if($role->description)
                                                                <p class="text-xs text-slate-500 mt-0.5">{{ $role->description }}</p>
                                                            @endif
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-sm text-slate-500">{{ __('Nenhuma função disponível') }}</p>
                                        @endif
                                    </div>

                                    <div class="text-xs mt-2 text-slate-500">
                                        {{ __('Selecione as funções que serão automaticamente atribuídas aos utilizadores quando obtiverem esta licença.') }}
                                    </div>

                                    @error('roles')
                                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Requirements Tab -->
                    <div x-show="activeTab === 'requirements'" x-transition>
                        <div class="space-y-6">
                            <!-- Required Certifications -->
                            <div class="card" x-show="showCertifications">
                                <h3 class="text-lg font-semibold mb-4">{{ __('Certificações Obrigatórias') }}</h3>

                                <div>
                                    <label class="block text-sm font-medium mb-2" for="required_certifications">
                                        {{ __('Certificações Obrigatórias') }}
                                    </label>
                                    <select name="required_certifications[]" id="required_certifications"
                                            class="form-select w-full choices @error('required_certifications') border-rose-300 @enderror"
                                            multiple>
                                        @if(isset($certifications))
                                            @foreach($certifications as $certification)
                                                <option value="{{ $certification->id }}"
                                                        @if(old('required_certifications') && in_array($certification->id, old('required_certifications')))
                                                            selected
                                                        @elseif(isset($license->id) && $license->requiredCertifications->contains($certification->id))
                                                            selected
                                                        @endif>
                                                    {{ $certification->name }}
                                                    @if($certification->acronym)
                                                        ({{ $certification->acronym }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="text-xs mt-2 text-slate-500">
                                        {{ __('Selecione as certificações que um indivíduo deve ter antes de solicitar esta licença') }}
                                    </div>
                                    @error('required_certifications')
                                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Official Documents - Role Based -->
                            <div class="card">
                                <h3 class="text-lg font-semibold mb-4">{{ __('Requisitos de Documentos Oficiais') }}</h3>

                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               id="requires_official_documents"
                                               name="requires_official_documents"
                                               class="form-checkbox"
                                               @change="showDocuments = $event.target.checked"
                                               {{ old('requires_official_documents', $license->requires_official_documents) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm">{{ __('Exigir Documentos Oficiais') }}</span>
                                    </label>
                                    <div class="text-xs mt-1 text-slate-500">
                                        {{ __('Marque se documentos oficiais devem ser submetidos antes de adquirir esta licença.') }}
                                    </div>
                                    @error('requires_official_documents')
                                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div x-show="showDocuments" x-transition class="mt-4 space-y-6">
                                    <div class="information-box mb-4">
                                        <svg class="inline-block w-4 h-4 mr-1 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ __('Importante: O indivíduo deve ter TODOS os documentos selecionados na sua categoria para adquirir esta licença.') }}
                                    </div>

                                    @php
                                        $documentsByCategory = App\Enums\OfficialDocumentTypeEnum::groupByCategory();
                                        $selectedAthleteTypes = old('required_athlete_documents', $license->required_athlete_documents ?? []);
                                        $selectedCoachTypes = old('required_coach_documents', $license->required_coach_documents ?? []);
                                        $selectedOfficialTypes = old('required_official_documents', $license->required_official_documents ?? []);
                                        $selectedDivingProfessionalTypes = old('required_diving_professional_documents', $license->required_diving_professional_documents ?? []);
                                    @endphp

                                    <!-- Athlete Documents -->
                                    <div class="border border-slate-200 rounded-lg p-4">
                                        <h4 class="font-semibold text-sm mb-3 text-slate-700 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            {{ __('Atletas') }}
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach($documentsByCategory['athlete'] as $docType)
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                           name="required_athlete_documents[]"
                                                           value="{{ $docType->value }}"
                                                           class="form-checkbox h-4 w-4"
                                                           {{ in_array($docType->value, $selectedAthleteTypes) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm">
                                                        {{ __(App\Enums\OfficialDocumentTypeEnum::toString($docType)) }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('required_athlete_documents')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Coach Documents -->
                                    <div class="border border-slate-200 rounded-lg p-4">
                                        <h4 class="font-semibold text-sm mb-3 text-slate-700 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                            </svg>
                                            {{ __('Treinadores') }}
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach($documentsByCategory['coach'] as $docType)
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                           name="required_coach_documents[]"
                                                           value="{{ $docType->value }}"
                                                           class="form-checkbox h-4 w-4"
                                                           {{ in_array($docType->value, $selectedCoachTypes) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm">
                                                        {{ __(App\Enums\OfficialDocumentTypeEnum::toString($docType)) }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('required_coach_documents')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Technical Officials Documents -->
                                    <div class="border border-slate-200 rounded-lg p-4">
                                        <h4 class="font-semibold text-sm mb-3 text-slate-700 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                            </svg>
                                            {{ __('Oficiais Técnicos') }}
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @php
                                                // Combine referee-judge and team-official documents
                                                $officialDocs = array_merge(
                                                    $documentsByCategory['referee-judge'] ?? [],
                                                    $documentsByCategory['team-official'] ?? []
                                                );
                                                // Remove duplicates based on value
                                                $officialDocs = collect($officialDocs)->unique(fn($doc) => $doc->value)->values()->all();
                                            @endphp
                                            @foreach($officialDocs as $docType)
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                           name="required_official_documents[]"
                                                           value="{{ $docType->value }}"
                                                           class="form-checkbox h-4 w-4"
                                                           {{ in_array($docType->value, $selectedOfficialTypes) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm">
                                                        {{ __(App\Enums\OfficialDocumentTypeEnum::toString($docType)) }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('required_official_documents')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Diving Professional Documents -->
                                    <div class="border border-slate-200 rounded-lg p-4">
                                        <h4 class="font-semibold text-sm mb-3 text-slate-700 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                            </svg>
                                            {{ __('licenses.diving_professionals') }}
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach($documentsByCategory['diving-professional'] as $docType)
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                           name="required_diving_professional_documents[]"
                                                           value="{{ $docType->value }}"
                                                           class="form-checkbox h-4 w-4"
                                                           {{ in_array($docType->value, $selectedDivingProfessionalTypes) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm">
                                                        {{ __(App\Enums\OfficialDocumentTypeEnum::toString($docType)) }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('required_diving_professional_documents')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Tab -->
                    <div x-show="activeTab === 'pricing'" x-transition>
                        <div class="space-y-6">
                            <div class="card">
                                <h3 class="text-lg font-semibold mb-4">{{ __('Preços') }}</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="unit_value_individual">
                                            {{ __('Preço para Individual (€)') }}
                                        </label>
                                        <input type="text"
                                               name="unit_value_individual"
                                               id="unit_value_individual"
                                               class="form-input w-full @error('unit_value_individual') border-rose-300 @enderror"
                                               pattern="^\$?(([1-9](\d*|\d{0,2}(,\d{3})*))|0)(\.\d{1,2})?$"
                                               value="{{ old('unit_value_individual', $license->unit_value_individual) }}">
                                        <div class="text-xs mt-1 text-slate-500">
                                            {{ __('Preço quando adquirido por indivíduos.') }}
                                        </div>
                                        @error('unit_value_individual')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="unit_value_entity">
                                            {{ __('Preço para Entidade (€)') }}
                                        </label>
                                        <input type="text"
                                               name="unit_value_entity"
                                               id="unit_value_entity"
                                               class="form-input w-full @error('unit_value_entity') border-rose-300 @enderror"
                                               pattern="^\$?(([1-9](\d*|\d{0,2}(,\d{3})*))|0)(\.\d{1,2})?$"
                                               value="{{ old('unit_value_entity', $license->unit_value_entity) }}">
                                        <div class="text-xs mt-1 text-slate-500">
                                            {{ __('Preço quando adquirido por entidades.') }}
                                        </div>
                                        @error('unit_value_entity')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="unit_value_federation">
                                            {{ __('Preço para Federação (€)') }}
                                        </label>
                                        <input type="text"
                                               name="unit_value_federation"
                                               id="unit_value_federation"
                                               class="form-input w-full @error('unit_value_federation') border-rose-300 @enderror"
                                               pattern="^\$?(([1-9](\d*|\d{0,2}(,\d{3})*))|0)(\.\d{1,2})?$"
                                               value="{{ old('unit_value_federation', $license->unit_value_federation) }}">
                                        <div class="text-xs mt-1 text-slate-500">
                                            {{ __('Preço quando adquirido por federações.') }}
                                        </div>
                                        @error('unit_value_federation')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="tax_percentage">
                                            {{ __('Taxa %') }}
                                        </label>
                                        <input id="tax_percentage"
                                               class="form-input w-full @error('tax_percentage') border-rose-300 @enderror"
                                               type="number"
                                               min="0"
                                               max="100"
                                               name="tax_percentage"
                                               value="{{ old('tax_percentage', $license->tax_percentage) }}">
                                        <div class="text-xs mt-1 text-slate-500">
                                            {{ __('Deixe vazio se não houver taxa') }}
                                        </div>
                                        @error('tax_percentage')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="block text-sm font-medium mb-1" for="moloni_reference">
                                        {{ __('moloni.product_reference') }}
                                    </label>
                                    <input id="moloni_reference"
                                           class="form-input w-full max-w-xs @error('moloni_reference') border-rose-300 @enderror"
                                           type="text"
                                           name="moloni_reference"
                                           maxlength="50"
                                           value="{{ old('moloni_reference', $license->moloni_reference) }}">
                                    <div class="text-xs mt-1 text-slate-500">
                                        {{ __('moloni.product_reference_help') }}
                                    </div>
                                    @error('moloni_reference')
                                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Availability Tab -->
                    <div x-show="activeTab === 'availability'" x-transition>
                        <div class="space-y-6">
                            <!-- Federations -->
                            <div class="card" x-show="showFederations">
                                <h3 class="text-lg font-semibold mb-4">{{ __('Federações') }}</h3>
                                <p class="text-sm text-slate-600 mb-4">
                                    {{ __('Selecione as federações que podem oferecer esta licença aos seus membros') }}
                                </p>

                                @php
                                    $selectedFederations = old('federation_ids', $license->federations ? $license->federations->pluck('id')->toArray() : []);
                                @endphp

                                <div class="bg-slate-50 rounded-lg p-4">
                                    @if(isset($federations) && $federations->count() > 0)
                                        <div class="mb-3">
                                            <label class="flex items-center font-semibold text-slate-700">
                                                <input type="checkbox"
                                                       id="select-all-federations"
                                                       class="form-checkbox h-4 w-4 text-blue-600"
                                                       @change="toggleAllFederations()">
                                                <span class="ml-2">{{ __('Selecionar Todas') }}</span>
                                            </label>
                                        </div>
                                        <hr class="my-2 border-slate-200">
                                        <div class="space-y-2 max-h-64 overflow-y-auto">
                                            @foreach($federations as $federation)
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                           name="federation_ids[]"
                                                           value="{{ $federation->id }}"
                                                           class="form-checkbox h-4 w-4 text-blue-600 federation-checkbox"
                                                           @if(in_array($federation->id, $selectedFederations)) checked @endif>
                                                    <span class="ml-2 text-sm">
                                                        {{ $federation->name }}
                                                        @if($federation->is_default_federation)
                                                            <span class="text-xs text-blue-600">({{ __('Principal') }})</span>
                                                        @endif
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-slate-500">{{ __('Nenhuma federação disponível') }}</p>
                                    @endif
                                </div>

                                <div class="text-xs mt-2 text-slate-500">
                                    {{ __('Se nenhuma federação for selecionada, a licença não estará disponível para compra.') }}
                                </div>
                                @error('federation_ids')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- License Duration -->
                            <div class="card">
                                <h3 class="text-lg font-semibold mb-4">{{ __('Duração da Licença') }}</h3>

                                <div class="information-box mb-4">
                                    <svg class="h-5 w-5 text-blue-400 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-medium">{{ __('Opções de Validade:') }}</span>
                                    <ul class="list-disc list-inside mt-2 space-y-1 text-sm">
                                        <li>{{ __('Duração Fixa: Válida pelo período especificado a partir da data de ativação') }}</li>
                                        <li>{{ __('Ano Civil: Para licenças anuais, opção para expirar em 31 de dezembro') }}</li>
                                        <li>{{ __('Sem Duração: Deixe os campos vazios para licenças sem data de expiração') }}</li>
                                    </ul>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="interval">
                                            {{ __('Duração') }}
                                        </label>
                                        <input id="interval"
                                               class="form-input w-full @error('interval') border-rose-300 @enderror"
                                               type="number"
                                               min="1"
                                               name="interval"
                                               value="{{ old('interval', $license->interval) }}"
                                               placeholder="{{ __('Deixe vazio para sem expiração') }}"
                                               @input="updateDurationExplanation()">
                                        @error('interval')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="interval_unit">
                                            {{ __('Unidade de Duração') }}
                                        </label>
                                        <select name="interval_unit" id="interval_unit"
                                                class="form-select w-full @error('interval_unit') border-rose-300 @enderror"
                                                @change="updateDurationExplanation()">
                                            <option value="">{{ __('-- Sem expiração --') }}</option>
                                            @foreach($intervalUnit as $key => $type)
                                                <option value="{{ $key }}"
                                                        @if(old('interval_unit', $license->interval_unit) == $key) selected @endif>
                                                    {{ $type }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('interval_unit')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div x-show="showValidityType">
                                        <label class="block text-sm font-medium mb-1" for="validity_type">
                                            {{ __('Tipo de Validade') }}
                                        </label>
                                        <select name="validity_type" id="validity_type"
                                                class="form-select w-full @error('validity_type') border-rose-300 @enderror"
                                                @change="updateDurationExplanation()">
                                            <option value="fixed_duration"
                                                    @if(old('validity_type', $license->validity_type ?? 'fixed_duration') == 'fixed_duration') selected @endif>
                                                {{ __('Duração Fixa') }}
                                            </option>
                                            <option value="calendar_year"
                                                    @if(old('validity_type', $license->validity_type) == 'calendar_year') selected @endif>
                                                {{ __('Ano Civil') }}
                                            </option>
                                        </select>
                                        @error('validity_type')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div x-show="durationExplanation" class="mt-3 text-sm text-slate-600 bg-slate-50 rounded p-3">
                                    <span x-text="durationExplanation"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Settings Tab -->
                    <div x-show="activeTab === 'advanced'" x-transition>
                        <div class="space-y-6">
                            <div class="card">
                                <h3 class="text-lg font-semibold mb-4">{{ __('licenses.advanced_settings') }}</h3>

                                <div class="space-y-4">
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               id="is_school_license"
                                               name="is_school_license"
                                               class="form-checkbox"
                                               {{ old('is_school_license', $license->is_school_license) ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <span class="text-sm font-medium">{{ __('Licença de Escola') }}</span>
                                            <div class="text-xs text-slate-500">
                                                {{ __('Marque se esta licença designa uma entidade como escola.') }}
                                            </div>
                                        </div>
                                    </label>
                                    @error('is_school_license')
                                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                    @enderror

                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $license->committee?->isInternational() ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $license->committee?->isInternational() ? __('International') : __('National') }}
                                        </span>
                                        <div>
                                            <span class="text-sm font-medium">{{ __('licenses.is_international_label') }}</span>
                                            <div class="text-xs text-slate-500">
                                                {{ __('Determined by the selected committee') }}
                                            </div>
                                        </div>
                                    </div>

                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               id="allow_entity_group_request"
                                               name="allow_entity_group_request"
                                               class="form-checkbox"
                                               {{ old('allow_entity_group_request', $license->allow_entity_group_request) ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <span class="text-sm font-medium">{{ __('Permitir compra em grupo por entidades') }}</span>
                                            <div class="text-xs text-slate-500">
                                                {{ __('Marque se as entidades podem comprar esta licença para os seus membros.') }}
                                            </div>
                                        </div>
                                    </label>
                                    @error('allow_entity_group_request')
                                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Footer -->
                <footer>
                    <div class="flex flex-col px-6 py-5 border-t border-slate-200">
                        <div class="flex gap-4 self-start">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Guardar registo') }}
                            </button>
                            <a class="btn btn-info" href="{{ route(Request::segment(1).'.license.index') }}">
                                {{ __('Voltar') }}
                            </a>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</div>

@php
    // Build professional role mapping from PHP data
    $professionalRoleMappingData = [];
    if (isset($professionalRoles)) {
        $professionalRoleMappingData = $professionalRoles->mapWithKeys(function($role) {
            $docCategory = null;
            if (in_array($role->role, ['INSTRUCTOR', 'LEADER'])) {
                $docCategory = 'instructor-leader';
            } elseif ($role->role === 'DIVER') {
                $docCategory = 'diver';
            } elseif ($role->role === 'ATHLETE') {
                $docCategory = 'athlete';
            } elseif ($role->role === 'COACH') {
                $docCategory = 'coach';
            } elseif ($role->role === 'TECHNICAL_OFFICIAL') {
                $docCategory = 'technical-official';
            }
            return [$role->id => $docCategory];
        })->filter()->toArray();
    }
@endphp

<script>
function licenseFormTabs() {
    return {
        activeTab: 'basic',
        showCertifications: false,
        showDocuments: {{ old('requires_official_documents', $license->requires_official_documents) ? 'true' : 'false' }},
        showEntityDocs: false,
        showIndividualDocs: false,
        showFederations: true,
        showValidityType: false,
        durationExplanation: '',
        professionalRoleMapping: @json($professionalRoleMappingData),

        init() {
            this.handleLicenseTypeChange();
            this.handleRequesterChange();
            this.updateDurationExplanation();
            this.checkAllFederationsStatus();

            // Add event listeners for federation checkbox changes
            document.querySelectorAll('.federation-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', () => this.checkAllFederationsStatus());
            });
        },

        checkAllFederationsStatus() {
            const selectAll = document.getElementById('select-all-federations');
            if (!selectAll) return;
            const checkboxes = document.querySelectorAll('.federation-checkbox');
            const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
            selectAll.checked = allChecked;
        },

        handleLicenseTypeChange() {
            const licenseType = document.getElementById('license_type');
            if (!licenseType) return;

            const selectedOption = licenseType.options[licenseType.selectedIndex];
            const licenseTypeName = selectedOption ? selectedOption.text.toLowerCase() : '';

            // Show certifications only for individual licenses
            this.showCertifications = licenseTypeName.includes('individual') || licenseTypeName.includes('indivíduo');

            // Update document categories
            this.showEntityDocs = licenseTypeName.includes('entidade') || licenseTypeName.includes('entity');
            this.showIndividualDocs = licenseTypeName.includes('individual') || licenseTypeName.includes('indivíduo');
        },

        handleRequesterChange() {
            const checkboxes = document.querySelectorAll('input[name="requester_model[]"]:checked');
            const checkedValues = Array.from(checkboxes).map(cb => cb.value);

            // Show federations unless only Federation is selected
            this.showFederations = !(checkedValues.length === 1 && checkedValues.includes('Federation'));
        },

        handleProfessionalRoleChange() {
            // Update document categories based on professional role
            // This would be implemented based on the professional role selected
        },

        toggleAllFederations() {
            const selectAll = document.getElementById('select-all-federations');
            const checkboxes = document.querySelectorAll('.federation-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        },

        updateDurationExplanation() {
            const interval = document.getElementById('interval').value;
            const unit = document.getElementById('interval_unit').value;
            const validityType = document.getElementById('validity_type')?.value || 'fixed_duration';

            this.showValidityType = unit === 'years' && interval && interval > 0;

            if (!interval || !unit) {
                this.durationExplanation = interval || unit ? '' : '{{ __("Esta licença não terá data de expiração.") }}';
                return;
            }

            // Calculate the approximate end date for preview
            const today = new Date();
            let endDate = new Date(today);
            const intervalNum = parseInt(interval);

            if (unit === 'years' && validityType === 'calendar_year') {
                if (interval === '1') {
                    endDate = new Date(today.getFullYear(), 11, 31); // Dec 31 of current year
                    const endDateStr = endDate.toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' });
                    this.durationExplanation = '{{ __("Esta licença será válida desde a ativação até 31 de dezembro do ano corrente.") }} (' + endDateStr + ')';
                } else {
                    const targetYear = today.getFullYear() + intervalNum - 1;
                    endDate = new Date(targetYear, 11, 31);
                    const endDateStr = endDate.toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' });
                    this.durationExplanation = '{{ __("Esta licença será válida desde a ativação até 31 de dezembro do ano :year.") }}'.replace(':year', targetYear) + ' (' + endDateStr + ')';
                }
            } else {
                // Calculate end date based on unit
                if (unit === 'years') {
                    endDate.setFullYear(endDate.getFullYear() + intervalNum);
                } else if (unit === 'months') {
                    endDate.setMonth(endDate.getMonth() + intervalNum);
                } else if (unit === 'weeks') {
                    endDate.setDate(endDate.getDate() + (intervalNum * 7));
                }

                const endDateStr = endDate.toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' });

                const unitText = {
                    'years': interval == 1 ? '{{ __("ano") }}' : '{{ __("anos") }}',
                    'months': interval == 1 ? '{{ __("mês") }}' : '{{ __("meses") }}',
                    'weeks': interval == 1 ? '{{ __("semana") }}' : '{{ __("semanas") }}'
                };

                if (unitText[unit]) {
                    this.durationExplanation = `{{ __("Esta licença será válida por") }} ${interval} ${unitText[unit]}. {{ __("Data de expiração aproximada:") }} ${endDateStr}`;
                }
            }
        },

        getVisibleDocCategories() {
            // This would return the visible document categories based on professional role
            return [];
        },

        getCategoryTitle(category) {
            const titles = {
                'instructor-leader': '{{ __("Documentos de Instrutor e Líder") }}',
                'diver': '{{ __("Documentos de Mergulhador") }}',
                'athlete': '{{ __("Documentos de Atleta") }}',
                'coach': '{{ __("Documentos de Treinador") }}',
                'referee-judge': '{{ __("Documentos de Árbitro/Juiz") }}'
            };
            return titles[category] || category;
        }
    };
}
</script>