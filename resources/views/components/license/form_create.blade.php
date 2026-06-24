<div class="sm:flex sm:space-x-4">
    <div class="mb-8 sm:w-full">
        <div class="bg-white shadow-lg rounded-sm flex flex-col md:flex-row md:-mr-px">
            <div class="grow">

                <!-- Panel body -->
                <div class="p-6 space-y-6">

                    <!-- Assignment -->
                    <section>

                        <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('Informação') }}</h3>

                        <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                            <div class="sm:w-full flex items-start">
                                {{-- Display current logo if it exists --}}
                                @if($license->hasMedia('logo'))
                                    <div class="mr-4 flex-shrink-0">
                                        <label class="block text-sm font-medium mb-1">{{ __('Logo Atual') }}</label>
                                        <img src="{{ $license->getFirstMediaUrl('logo', 'thumb') }}"
                                             alt="{{ $license->name ?: 'License' }} Logo"
                                             class="w-16 h-16 object-contain rounded-lg shadow-md border border-slate-200">
                                    </div>
                                @endif

                                {{-- Input for uploading new logo --}}
                                <div class="flex-grow">
                                    <label class="block text-sm font-medium mb-1" for="logo">
                                        {{ $license->hasMedia('logo') ? __('Carregar Novo Logo') : __('Imagem/Logo da Licença') }}
                                    </label>
                                    <input type="file" name="logo" id="logo" class="form-input w-full @error('logo') border-rose-300 @enderror">
                                    <div class="text-xs mt-1 text-slate-500">
                                        {{ __('Deixe em branco para manter o logo atual. Apenas ficheiros JPG ou PNG.') }}
                                    </div>
                                    @error('logo')
                                        <div class="text-xs mt-1 text-rose-500">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="sm:flex space-y-4 sm:space-y-0 sm:space-x-4 mt-5 items-start">

                            <div class="sm:w-1/2">
                                <label class="block text-sm font-medium mb-1" for="name"> {{ __('Nome da Licença') }} <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" name="name" id="name"
                                       class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                                       value="{{old('name', $license->name)}}" required>

                                @if($errors->has('name'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('name') }}
                                    </div>
                                @endif
                            </div>

                            <div class="sm:w-1/3">
                                <label class="block text-sm font-medium mb-1"
                                       for="license_code"> {{ __('Código da Licença') }}</label>
                                <input type="text" name="license_code" id="license_code"
                                       class="form-input w-full {{ $errors->has('license_code') ? 'border-rose-300' : '' }}"
                                       value="{{old('license_code', $license->license_code)}}" required>

                                @if($errors->has('license_code'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('license_code') }}
                                    </div>
                                @endif
                            </div>

                            <!-- Requester Model -->
                            <div class="sm:w-1/3">
                                <label class="block text-sm font-medium mb-1">{{ __('Requerente') }}
                                    <span class="text-rose-500">*</span></label>
                                <div class="space-y-2">
                                    @php
                                        $currentRequesterModel = old('requester_model', $license->requester_model ?? []);
                                        if (!is_array($currentRequesterModel)) {
                                            $currentRequesterModel = [];
                                        }
                                        // Normalize to lowercase for comparison
                                        $currentRequesterModelLower = array_map('strtolower', $currentRequesterModel);
                                    @endphp
                                    @foreach($requesterModels as $requester => $val)
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="requester_model[]" 
                                                   value="{{ $val }}"
                                                   class="form-checkbox h-4 w-4 text-blue-600 {{ $errors->has('requester_model') ? 'border-rose-300' : '' }}"
                                                   @if(in_array(strtolower($val), $currentRequesterModelLower)) checked @endif>
                                            <span class="ml-2 text-sm">{{ $requester }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <div class="text-xs mt-1"> {{ __('*Escolha quem pode solicitar esta licença') }} </div>

                                @if($errors->has('requester_model'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('requester_model') }}
                                    </div>
                                @endif
                            </div>

                        </div>

                        <!-- Required Certifications (shows only for Individual requester) -->
                        <div id="required-certifications-section" class="mt-5" style="display: none;">
                            <div class="sm:w-full">
                                <label class="block text-sm font-medium mb-1" for="required_certifications">
                                    {{ __('Certificações Obrigatórias') }}
                                </label>
                                <select name="required_certifications[]" id="required_certifications"
                                        class="form-input w-full choices {{ $errors->has('required_certifications') ? 'border-rose-300' : '' }}"
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
                                <div class="text-xs mt-1">
                                    {{ __('Selecione as certificações que um indivíduo deve ter antes de solicitar esta licença') }}
                                </div>

                                @if($errors->has('required_certifications'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('required_certifications') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Role Assignments -->
                        <div class="mt-5">
                            <div class="sm:w-full">
                                <label class="block text-sm font-medium mb-1" for="roles">
                                    {{ __('admin.role_mappings.roles') }}
                                </label>
                                <select name="roles[]" id="roles"
                                        class="form-input w-full choices {{ $errors->has('roles') ? 'border-rose-300' : '' }}"
                                        multiple>
                                    @if(isset($roles))
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}"
                                                @if(old('roles') && in_array($role->id, old('roles')))
                                                    selected
                                                @elseif(isset($license->id) && $license->roles->contains($role->id))
                                                    selected
                                                @endif>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                    @endif
                                </select>
                                <div class="text-xs mt-1">
                                    {{ __('admin.role_mappings.license.select_roles_help') }}
                                </div>

                                @if($errors->has('roles'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('roles') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                            <div class="sm:w-1/3">
                                <label class="block text-sm font-medium mb-1" for="committee_id">{{ __('Comité') }}
                                    <span class="text-rose-500">*</span></label>
                                <select name="committee_id" id="committee_id"
                                        class="form-input w-full {{ $errors->has('committee_id') ? 'border-rose-300' : '' }}"
                                        required>
                                    <option value="" selected disabled> {{ __('-- Selecione uma opção --') }} </option>
                                    @foreach($committees as $committee)
                                        <option value="{{ $committee->id }}"
                                                @if(old('committee_id', $license->committee_id) == $committee->id) selected @endif>{{ $committee->name }}</option>
                                    @endforeach
                                </select>

                                @if($errors->has('committee_id'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('committee_id') }}
                                    </div>
                                @endif
                            </div>

                            <div class="sm:w-1/3">
                                <label class="block text-sm font-medium mb-1" for="license_type"> {{ __('Tipo') }} <span
                                        class="text-rose-500">*</span></label>

                                <select name="type_id" id="license_type"
                                        class="form-input w-full {{ $errors->has('type_id') ? 'border-rose-300' : '' }}"
                                        required>
                                    <option value="" selected disabled> {{ __('-- Selecione uma opção --') }} </option>
                                    @foreach($licenseTypes as $type)
                                        <option value="{{ $type->id }}"
                                                @if(old('type_id', $license->type_id) == $type->id) selected @endif>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                <div class="text-xs mt-1"> {{ __('entidade ou individual?') }} </div>
                                @if($errors->has('type_id'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('type_id') }}
                                    </div>
                                @endif
                            </div>

                            <div class="sm:w-1/3">
                                <label class="block text-sm font-medium mb-1"
                                       for="function_id"> {{ __('Função') }}</label>

                                <select name="function_id" id="function_id"
                                        class="form-input w-full {{ $errors->has('function_id') ? 'border-rose-300' : '' }}">
                                    <option selected value=""></option>
                                    @foreach($professionalRoles as $type)
                                        <option value="{{ $type->id }}"
                                                @if(old('function_id', $license->professional_role_id) == $type->id) selected @endif>{{ $type->name }}</option>
                                    @endforeach
                                </select>

                                <div class="text-xs mt-1"> {{ __('para que tipo é esta licença') }} </div>

                                @if($errors->has('function_id'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('function_id') }}
                                    </div>
                                @endif
                            </div>

                            <div class="sm:w-1/4">
                                <label class="block text-sm font-medium mb-1" for="sport_id"> {{ __('Desporto') }}</label>

                                <select name="sport_id" id="sport_id"
                                        class="form-input w-full {{ $errors->has('sport_id') ? 'border-rose-300' : '' }}">
                                    <option value="" selected disabled> {{ __('-- Selecione uma opção --') }} </option>
                                    @foreach($sports as $type)
                                        <option value="{{ $type->id }}"
                                                @if(old('sport_id', $license->sport_id) == $type->id) selected @endif>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                <div class="text-xs mt-1"> {{ __('(apenas se aplicável)') }} </div>
                                @if($errors->has('sport_id'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('sport_id') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:flex space-y-4 sm:space-y-0 sm:space-x-4 mt-5 items-start">
                            <div class="mt-5">
                                <label for="is_school_license" class="flex items-center">
                                    <input type="checkbox" id="is_school_license" name="is_school_license"
                                           class="form-checkbox"
                                        {{ old('is_school_license', $license->is_school_license) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm">{{ __('Licença de Escola') }}</span>
                                </label>
                                <div
                                    class="text-xs mt-1">{{ __('Marque se esta licença designa uma entidade como escola.') }}</div>

                                @if($errors->has('is_school_license'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('is_school_license') }}
                                    </div>
                                @endif
                            </div>

                            <div class="mt-5">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $license->committee?->isInternational() ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $license->committee?->isInternational() ? __('International') : __('National') }}
                                    </span>
                                    <span class="text-sm">{{ __('licenses.is_international_label') }}</span>
                                </div>
                                <div class="text-xs mt-1 text-slate-500">{{ __('Determined by the selected committee') }}</div>
                            </div>

                            <div class="mt-5">
                                <label for="allow_entity_group_request" class="flex items-center">
                                    <input type="checkbox" id="allow_entity_group_request" name="allow_entity_group_request"
                                           class="form-checkbox"
                                        {{ old('allow_entity_group_request', $license->allow_entity_group_request) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm">{{ __('Permitir compra em grupo por entidades') }}</span>
                                </label>
                                <div
                                    class="text-xs mt-1">{{ __('Marque se as entidades podem comprar esta licença para os seus membros.') }}</div>

                                @if($errors->has('allow_entity_group_request'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('allow_entity_group_request') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <hr class="my-6">

                        <div id="federation-section">
                            <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('Federações') }}</h3>
                            <p class="text-sm text-slate-600 mb-4">{{ __('Selecione as federações que podem oferecer esta licença aos seus membros') }}</p>

                            <div class="mt-5">
                            <label class="block text-sm font-medium mb-2">{{ __('Federações Disponíveis') }}</label>
                            
                            @php
                                $selectedFederations = old('federation_ids', $license->federations ? $license->federations->pluck('id')->toArray() : []);
                            @endphp
                            
                            <div class="space-y-2 max-h-64 overflow-y-auto border border-slate-200 rounded p-3">
                                @if(isset($federations) && $federations->count() > 0)
                                    <div class="mb-2">
                                        <label class="flex items-center font-semibold text-slate-700">
                                            <input type="checkbox" 
                                                   id="select-all-federations" 
                                                   class="form-checkbox h-4 w-4 text-blue-600 mr-2">
                                            <span>{{ __('Selecionar Todas') }}</span>
                                        </label>
                                    </div>
                                    <hr class="my-2">
                                    @foreach($federations as $federation)
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="federation_ids[]" 
                                                   value="{{ $federation->id }}"
                                                   class="form-checkbox h-4 w-4 text-blue-600 federation-checkbox {{ $errors->has('federation_ids') ? 'border-rose-300' : '' }}"
                                                   @if(in_array($federation->id, $selectedFederations)) checked @endif>
                                            <span class="ml-2 text-sm">
                                                {{ $federation->name }}
                                                @if($federation->is_default_federation)
                                                    <span class="text-xs text-blue-600">({{ __('Principal') }})</span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                @else
                                    <p class="text-sm text-slate-500">{{ __('Nenhuma federação disponível') }}</p>
                                @endif
                            </div>
                            
                            <div class="text-xs mt-2 text-slate-500">
                                {{ __('Se nenhuma federação for selecionada, a licença não estará disponível para compra.') }}
                            </div>
                            
                            @if($errors->has('federation_ids'))
                                <div class="text-xs mt-1 text-rose-500">
                                    {{ $errors->first('federation_ids') }}
                                </div>
                            @endif
                            </div>
                        </div>

                        <hr class="my-6">

                        <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('Preços') }}</h3>

                        <div
                            class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5 border-b border-slate-200 pb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1"
                                       for="unit_value_individual"> {{ __('Preço para Individual (€)') }}</label>
                                <input type="text"
                                       name="unit_value_individual"
                                       id="unit_value_individual"
                                       class="form-input w-full {{ $errors->has('unit_value_individual') ? 'border-rose-300' : '' }}"
                                       pattern="^\$?(([1-9](\d*|\d{0,2}(,\d{3})*))|0)(\.\d{1,2})?$"
                                       value="{{ old('unit_value_individual', $license->unit_value_individual) }}">
                                <div
                                    class="text-xs mt-1"> {{ __('Preço quando adquirido por indivíduos.') }} </div>

                                @if($errors->has('unit_value_individual'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('unit_value_individual') }}
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1"
                                       for="unit_value_entity"> {{ __('Preço para Entidade (€)') }}</label>
                                <input type="text"
                                       name="unit_value_entity"
                                       id="unit_value_entity"
                                       class="form-input w-full {{ $errors->has('unit_value_entity') ? 'border-rose-300' : '' }}"
                                       pattern="^\$?(([1-9](\d*|\d{0,2}(,\d{3})*))|0)(\.\d{1,2})?$"
                                       value="{{ old('unit_value_entity', $license->unit_value_entity) }}">
                                <div
                                    class="text-xs mt-1"> {{ __('Preço quando adquirido por entidades.') }} </div>

                                @if($errors->has('unit_value_entity'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('unit_value_entity') }}
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1"
                                       for="unit_value_federation"> {{ __('Preço para Federação (€)') }}</label>
                                <input type="text"
                                       name="unit_value_federation"
                                       id="unit_value_federation"
                                       class="form-input w-full {{ $errors->has('unit_value_federation') ? 'border-rose-300' : '' }}"
                                       pattern="^\$?(([1-9](\d*|\d{0,2}(,\d{3})*))|0)(\.\d{1,2})?$"
                                       value="{{ old('unit_value_federation', $license->unit_value_federation) }}">
                                <div
                                    class="text-xs mt-1"> {{ __('Preço quando adquirido por federações.') }} </div>

                                @if($errors->has('unit_value_federation'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('unit_value_federation') }}
                                    </div>
                                @endif
                            </div>


                            <div>
                                <label class="block text-sm font-medium mb-1"
                                       for="tax_percentage">{{ __('Taxa %') }}</label>
                                <input id="tax_percentage"
                                       class="form-input w-full {{ $errors->has('tax_percentage') ? 'border-rose-300' : '' }}"
                                       type="number" min="0" max="100" name="tax_percentage"
                                       value="{{ old('tax_percentage', $license->tax_percentage) }}" />
                                <div class="text-xs mt-1"> {{ __('Deixe vazio se não houver taxa') }} </div>

                                @if($errors->has('tax_percentage'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('tax_percentage') }}
                                    </div>
                                @endif

                            </div>
                        </div>

                        <hr class="my-6">

                        <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('Requisitos de Documentos Oficiais') }}</h3>
                        
                        <div class="mt-5">
                            <label for="requires_official_documents" class="flex items-center">
                                <input type="checkbox" id="requires_official_documents" name="requires_official_documents"
                                       class="form-checkbox"
                                       onchange="toggleDocumentRequirements()"
                                    {{ old('requires_official_documents', $license->requires_official_documents) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm">{{ __('Exigir Documentos Oficiais') }}</span>
                            </label>
                            <div class="text-xs mt-1">{{ __('Marque se documentos oficiais devem ser submetidos antes de adquirir esta licença.') }}</div>

                            @if($errors->has('requires_official_documents'))
                                <div class="text-xs mt-1 text-rose-500 h-2">
                                    {{ $errors->first('requires_official_documents') }}
                                </div>
                            @endif
                        </div>
                        
                        <div id="document-requirements-section" class="mt-5 {{ old('requires_official_documents', $license->requires_official_documents) ? '' : 'hidden' }}">
                            <label class="block text-sm font-medium mb-1">{{ __('Tipos de Documentos Obrigatórios') }}</label>
                            <div class="text-xs text-amber-600 mb-2">
                                <svg class="inline-block w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('Importante: A entidade ou indivíduo deve ter TODOS os tipos de documentos selecionados para adquirir esta licença.') }}
                            </div>
                            <div class="bg-slate-50 rounded p-4">
                                @php
                                    $documentsByCategory = App\Enums\OfficialDocumentTypeEnum::groupByCategory();
                                    $selectedTypes = old('required_document_types', $license->required_document_types ?? []);
                                @endphp
                                
                                <!-- Entity Documents -->
                                <div class="mb-4" id="entity-documents-group">
                                    <h4 class="font-semibold text-sm mb-2 text-slate-700">{{ __('Documentos de Entidade') }}</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                        @foreach($documentsByCategory['entity'] as $docType)
                                            <label class="flex items-center">
                                                <input type="checkbox" 
                                                       name="required_document_types[]" 
                                                       value="{{ $docType->value }}"
                                                       class="form-checkbox entity-doc-type"
                                                       {{ in_array($docType->value, $selectedTypes) ? 'checked' : '' }}>
                                                <span class="ml-2 text-sm">{{ __(App\Enums\OfficialDocumentTypeEnum::toString($docType)) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <!-- Individual Documents by Role -->
                                <div id="individual-documents-groups">
                                    <!-- Instructor/Leader Documents -->
                                    <div class="mb-4 individual-doc-group" data-role="instructor-leader">
                                        <h4 class="font-semibold text-sm mb-2 text-slate-700">{{ __('Documentos de Instrutor e Líder') }}</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                            @foreach($documentsByCategory['instructor-leader'] as $docType)
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                           name="required_document_types[]" 
                                                           value="{{ $docType->value }}"
                                                           class="form-checkbox individual-doc-type"
                                                           {{ in_array($docType->value, $selectedTypes) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm">{{ __(App\Enums\OfficialDocumentTypeEnum::toString($docType)) }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <!-- Diver Documents -->
                                    <div class="mb-4 individual-doc-group" data-role="diver">
                                        <h4 class="font-semibold text-sm mb-2 text-slate-700">{{ __('Documentos de Mergulhador') }}</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                            @foreach($documentsByCategory['diver'] as $docType)
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                           name="required_document_types[]" 
                                                           value="{{ $docType->value }}"
                                                           class="form-checkbox individual-doc-type"
                                                           {{ in_array($docType->value, $selectedTypes) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm">{{ __(App\Enums\OfficialDocumentTypeEnum::toString($docType)) }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <!-- Athlete Documents -->
                                    <div class="mb-4 individual-doc-group" data-role="athlete">
                                        <h4 class="font-semibold text-sm mb-2 text-slate-700">{{ __('Documentos de Atleta') }}</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                            @foreach($documentsByCategory['athlete'] as $docType)
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                           name="required_document_types[]" 
                                                           value="{{ $docType->value }}"
                                                           class="form-checkbox individual-doc-type"
                                                           {{ in_array($docType->value, $selectedTypes) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm">{{ __(App\Enums\OfficialDocumentTypeEnum::toString($docType)) }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <!-- Coach Documents -->
                                    <div class="mb-4 individual-doc-group" data-role="coach">
                                        <h4 class="font-semibold text-sm mb-2 text-slate-700">{{ __('Documentos de Treinador') }}</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                            @foreach($documentsByCategory['coach'] as $docType)
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                           name="required_document_types[]" 
                                                           value="{{ $docType->value }}"
                                                           class="form-checkbox individual-doc-type"
                                                           {{ in_array($docType->value, $selectedTypes) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm">{{ __(App\Enums\OfficialDocumentTypeEnum::toString($docType)) }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <!-- Referee/Judge Documents -->
                                    <div class="mb-4 individual-doc-group" data-role="referee-judge">
                                        <h4 class="font-semibold text-sm mb-2 text-slate-700">{{ __('Documentos de Árbitro/Juiz') }}</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                            @foreach($documentsByCategory['referee-judge'] as $docType)
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                           name="required_document_types[]" 
                                                           value="{{ $docType->value }}"
                                                           class="form-checkbox individual-doc-type"
                                                           {{ in_array($docType->value, $selectedTypes) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm">{{ __(App\Enums\OfficialDocumentTypeEnum::toString($docType)) }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-xs mt-2 text-slate-600">
                                {{ __('Selecione todos os tipos de documentos que devem ser fornecidos e aprovados antes desta licença poder ser adquirida. Apenas as categorias relevantes serão mostradas com base no tipo de licença.') }}
                            </div>
                            
                            @if($errors->has('required_document_types'))
                                <div class="text-xs mt-1 text-rose-500">
                                    {{ $errors->first('required_document_types') }}
                                </div>
                            @endif
                        </div>

                        <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1 mt-6">{{ __('Duração da Licença') }}</h3>
                        
                        <!-- License Duration Notice -->
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mt-4 mb-5">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3 text-sm text-blue-700">
                                    <p class="font-medium mb-1">{{ __('Opções de Validade da Licença:') }}</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>{{ __('Duração Fixa: Válida pelo período especificado a partir da data de ativação') }}</li>
                                        <li>{{ __('Ano Civil: Para licenças anuais, opção para expirar em 31 de dezembro do ano alvo') }}</li>
                                        <li>{{ __('Sem Duração: Deixe os campos vazios para licenças sem data de expiração') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div
                            class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5 ">


                            <div>
                                <label class="block text-sm font-medium mb-1" for="interval">{{ __('Duração') }}</label>
                                <input id="interval"
                                       class="form-input w-full {{ $errors->has('interval') ? 'border-rose-300' : '' }}"
                                       type="number" min="1" name="interval"
                                       value="{{ old('interval', $license->interval) }}"
                                       placeholder="{{ __('Deixe vazio para sem expiração') }}" />
                                <div class="text-xs mt-1" id="interval-hint"> 
                                    {{ __('Insira o número da duração ou deixe vazio') }} 
                                </div>


                                @if($errors->has('interval'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('interval') }}
                                    </div>
                                @endif

                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1"
                                       for="interval_unit">{{ __('Unidade de Duração') }}</label>

                                <select name="interval_unit" id="interval_unit"
                                        class="form-select w-full {{ $errors->has('interval_unit') ? 'border-rose-300' : '' }}">
                                    <option value=""> {{ __('-- Sem expiração --') }} </option>
                                    @foreach($intervalUnit as $key=>$type)
                                        <option value="{{ $key }}"
                                                @if(old('interval_unit', $license->interval_unit) == $key) selected @endif>{{ $type }}</option>
                                    @endforeach
                                </select>
                                <div class="text-xs mt-1" id="unit-hint"> 
                                    {{ __('Selecione a unidade de tempo ou deixe vazio') }} 
                                </div>

                                @if($errors->has('interval_unit'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('interval_unit') }}
                                    </div>
                                @endif

                            </div>
                            
                            <!-- Validity Type - Only show when years is selected as unit -->
                            <div id="validity-type-container" class="hidden">
                                <label class="block text-sm font-medium mb-1"
                                       for="validity_type">{{ __('Tipo de Validade') }}</label>

                                <select name="validity_type" id="validity_type"
                                        class="form-select w-full {{ $errors->has('validity_type') ? 'border-rose-300' : '' }}">
                                    <option value="fixed_duration" 
                                            @if(old('validity_type', $license->validity_type ?? 'fixed_duration') == 'fixed_duration') selected @endif>
                                        {{ __('Duração Fixa') }}
                                    </option>
                                    <option value="calendar_year"
                                            @if(old('validity_type', $license->validity_type) == 'calendar_year') selected @endif>
                                        {{ __('Ano Civil') }}
                                    </option>
                                </select>
                                <div class="text-xs mt-1"> 
                                    {{ __('Como calcular a expiração') }} 
                                </div>

                                @if($errors->has('validity_type'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('validity_type') }}
                                    </div>
                                @endif
                            </div>

                        </div>
                        
                        <!-- Dynamic hint based on selection -->
                        <div id="duration-explanation" class="mt-3 text-sm text-slate-600 bg-slate-50 rounded p-3 hidden">
                            <span id="duration-text"></span>
                        </div>


                    </section>

                </div>

                <!-- Panel footer -->
                <footer>
                    <div class="flex flex-col px-6 py-5 border-t border-slate-200">
                        <div class="flex gap-4 self-start">

                            <button type="submit" class="btn btn-primary">
                                {{__('Guardar registo')}}
                            </button>

                            <a class="btn btn-info"
                               href="{{ route(Request::segment(1).'.license.index') }}"> {{ __('Voltar') }} </a>
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
            // Map role codes to document categories
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
function toggleDocumentRequirements() {
    const checkbox = document.getElementById('requires_official_documents');
    const section = document.getElementById('document-requirements-section');
    
    if (checkbox.checked) {
        section.classList.remove('hidden');
        updateDocumentCategories(); // Update visibility when showing
    } else {
        section.classList.add('hidden');
        // Uncheck all document type checkboxes when hiding
        const checkboxes = section.querySelectorAll('input[type="checkbox"][name="required_document_types[]"]');
        checkboxes.forEach(cb => cb.checked = false);
    }
}

// Build professional role mapping from PHP data
const professionalRoleMapping = @json($professionalRoleMappingData);

function updateDocumentCategories() {
    const licenseType = document.getElementById('license_type').value;
    const professionalRole = document.getElementById('function_id').value;
    const entityDocsGroup = document.getElementById('entity-documents-group');
    const individualDocsGroups = document.getElementById('individual-documents-groups');
    const individualDocGroups = document.querySelectorAll('.individual-doc-group');
    
    // Reset visibility
    entityDocsGroup.style.display = 'none';
    individualDocsGroups.style.display = 'none';
    individualDocGroups.forEach(group => group.style.display = 'none');
    
    // If no license type selected, show both groups as a default
    if (!licenseType || licenseType === '') {
        entityDocsGroup.style.display = 'block';
        individualDocsGroups.style.display = 'block';
        individualDocGroups.forEach(group => group.style.display = 'block');
        return;
    }
    
    // Get the selected option text to determine if it's entity or individual
    const licenseTypeSelect = document.getElementById('license_type');
    const selectedOption = licenseTypeSelect.options[licenseTypeSelect.selectedIndex];
    const licenseTypeName = selectedOption ? selectedOption.text.toLowerCase() : '';
    
    // Show based on license type name
    if (licenseTypeName.includes('entidade') || licenseTypeName.includes('entity')) { // Entity license
        entityDocsGroup.style.display = 'block';
        // Uncheck individual documents
        document.querySelectorAll('.individual-doc-type').forEach(cb => cb.checked = false);
    } else if (licenseTypeName.includes('individual') || licenseTypeName.includes('indivíduo')) { // Individual license
        individualDocsGroups.style.display = 'block';
        // Uncheck entity documents
        document.querySelectorAll('.entity-doc-type').forEach(cb => cb.checked = false);
        
        // Show specific role documents based on professional role
        if (professionalRole && professionalRoleMapping[professionalRole]) {
            const mappedRole = professionalRoleMapping[professionalRole];
            const roleGroup = document.querySelector(`.individual-doc-group[data-role="${mappedRole}"]`);
            if (roleGroup) {
                roleGroup.style.display = 'block';
            }
            
            // For instructor/leader roles, also show diver documents as they often need both
            if (mappedRole === 'instructor-leader') {
                const diverGroup = document.querySelector('.individual-doc-group[data-role="diver"]');
                if (diverGroup) {
                    diverGroup.style.display = 'block';
                }
            }
        } else {
            // If no professional role selected, show all individual categories
            individualDocGroups.forEach(group => group.style.display = 'block');
        }
    } else {
        // If we can't determine the type, show all options
        entityDocsGroup.style.display = 'block';
        individualDocsGroups.style.display = 'block';
        individualDocGroups.forEach(group => group.style.display = 'block');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for license type and professional role changes
    const licenseTypeSelect = document.getElementById('license_type');
    const professionalRoleSelect = document.getElementById('function_id');
    
    if (licenseTypeSelect) {
        licenseTypeSelect.addEventListener('change', function() {
            if (document.getElementById('requires_official_documents').checked) {
                updateDocumentCategories();
            }
            // Also update certifications visibility when license type changes
            updateCertificationsVisibility();
        });
    }
    
    if (professionalRoleSelect) {
        professionalRoleSelect.addEventListener('change', function() {
            if (document.getElementById('requires_official_documents').checked) {
                updateDocumentCategories();
            }
        });
    }
    
    // Initial update if documents are required
    if (document.getElementById('requires_official_documents').checked) {
        updateDocumentCategories();
    }
    
    // Handle certifications visibility
    const certificationsSection = document.getElementById('required-certifications-section');
    
    function updateCertificationsVisibility() {
        const licenseTypeSelect = document.getElementById('license_type');
        if (!licenseTypeSelect || !licenseTypeSelect.value) {
            certificationsSection.style.display = 'none';
            return;
        }
        
        // Get the selected option text to determine if it's individual type
        const selectedOption = licenseTypeSelect.options[licenseTypeSelect.selectedIndex];
        const licenseTypeName = selectedOption ? selectedOption.text.toLowerCase() : '';
        
        // Show certifications section only for individual license types
        if (licenseTypeName.includes('individual') || licenseTypeName.includes('indivíduo')) {
            certificationsSection.style.display = 'block';
        } else {
            certificationsSection.style.display = 'none';
        }
    }
    
    // Initial check for certifications visibility
    updateCertificationsVisibility();
    const intervalInput = document.getElementById('interval');
    const unitSelect = document.getElementById('interval_unit');
    const validityTypeContainer = document.getElementById('validity-type-container');
    const validityTypeSelect = document.getElementById('validity_type');
    const explanationDiv = document.getElementById('duration-explanation');
    const explanationText = document.getElementById('duration-text');
    
    function updateUI() {
        const interval = intervalInput.value;
        const unit = unitSelect.value;
        const validityType = validityTypeSelect.value;
        
        // Show/hide validity type based on selection
        if (unit === 'years' && interval && interval > 0) {
            validityTypeContainer.classList.remove('hidden');
        } else {
            validityTypeContainer.classList.add('hidden');
            // Reset to fixed_duration if not applicable
            validityTypeSelect.value = 'fixed_duration';
        }
        
        // Update explanation
        if (!interval && !unit) {
            explanationDiv.classList.add('hidden');
            return;
        }
        
        let explanation = '';
        
        if (interval && unit) {
            if (unit === 'years' && validityType === 'calendar_year') {
                if (interval === '1') {
                    explanation = @json(__("Esta licença será válida desde a ativação até 31 de dezembro do ano corrente."));
                } else {
                    const targetYear = new Date().getFullYear() + parseInt(interval) - 1;
                    explanation = @json(__("Esta licença será válida desde a ativação até 31 de dezembro do ano :year."))
                        .replace(':year', targetYear);
                }
            } else if (unit === 'years') {
                const yearText = interval == 1 ? @json(__("ano")) : @json(__("anos"));
                explanation = @json(__("Esta licença será válida por :years :yearText a partir da data de ativação."))
                    .replace(':years', interval)
                    .replace(':yearText', yearText);
            } else if (unit === 'months') {
                const monthText = interval == 1 ? @json(__("mês")) : @json(__("meses"));
                explanation = @json(__("Esta licença será válida por :months :monthText a partir da data de ativação."))
                    .replace(':months', interval)
                    .replace(':monthText', monthText);
            } else if (unit === 'weeks') {
                const weekText = interval == 1 ? @json(__("semana")) : @json(__("semanas"));
                explanation = @json(__("Esta licença será válida por :weeks :weekText a partir da data de ativação."))
                    .replace(':weeks', interval)
                    .replace(':weekText', weekText);
            }
        } else if (!interval && !unit) {
            explanation = @json(__("Esta licença não terá data de expiração."));
        }
        
        if (explanation) {
            explanationText.textContent = explanation;
            explanationDiv.classList.remove('hidden');
        } else {
            explanationDiv.classList.add('hidden');
        }
    }
    
    intervalInput.addEventListener('input', updateUI);
    unitSelect.addEventListener('change', updateUI);
    validityTypeSelect.addEventListener('change', updateUI);
    
    // Initial check
    updateUI();
});

// Handle requester model changes to show/hide federation section
function updateFederationSectionVisibility() {
    const requesterCheckboxes = document.querySelectorAll('input[name="requester_model[]"]');
    const federationSection = document.getElementById('federation-section');
    
    if (!federationSection) {
        return;
    }
    
    // Get checked requester models
    const checkedRequesters = Array.from(requesterCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);
    
    // Show federation section if:
    // 1. No requester is selected (show by default)
    // 2. Individual or Entity is selected (they need federation assignment)
    // Hide if only Federation is selected (federation doesn't need federation assignment)
    if (checkedRequesters.length === 0 || 
        checkedRequesters.includes('Entity') || 
        checkedRequesters.includes('Individual')) {
        federationSection.style.display = 'block';
    } else if (checkedRequesters.length === 1 && 
               checkedRequesters.includes('Federation')) {
        federationSection.style.display = 'none';
        // Uncheck all federation checkboxes when hiding
        const federationCheckboxes = document.querySelectorAll('.federation-checkbox');
        federationCheckboxes.forEach(cb => cb.checked = false);
    } else {
        // Mixed selection including Federation - show the section
        federationSection.style.display = 'block';
    }
}

// Handle "Select All" federations checkbox
document.addEventListener('DOMContentLoaded', function() {
    // Set up requester model change listeners
    const requesterCheckboxes = document.querySelectorAll('input[name="requester_model[]"]');
    
    requesterCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateFederationSectionVisibility);
    });
    
    // Initial visibility check
    updateFederationSectionVisibility();
    
    const selectAllCheckbox = document.getElementById('select-all-federations');
    const federationCheckboxes = document.querySelectorAll('.federation-checkbox');
    
    if (selectAllCheckbox && federationCheckboxes.length > 0) {
        // Check if all are already selected on page load
        const updateSelectAll = () => {
            const allChecked = Array.from(federationCheckboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
        };
        
        // Initial state
        updateSelectAll();
        
        // Handle select all click
        selectAllCheckbox.addEventListener('change', function() {
            federationCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Handle individual checkbox changes
        federationCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectAll);
        });
    }
});
</script>
