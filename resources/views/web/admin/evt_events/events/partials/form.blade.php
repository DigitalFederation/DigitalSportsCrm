@php
    $technicalDelegate = $event->technicalDelegate ?? null;
    $chiefJudge = $event->chiefJudge ?? null;
    $competitionDirector = $event->competitionDirector ?? null;
    $currentOrganizerId = $event->organizer
        ? (str_contains($event->organizer->organizable_type, 'Federation') ? 'federation_' : 'entity_') . $event->organizer->organizable_id
        : '';
@endphp

<div class="space-y-6" x-data="{
    statusClass: '{{ class_basename($event->status_class) }}',
    organizerType: '{{ old('organizer_type', optional($event->organizer)->organizable_type ? class_basename(optional($event->organizer)->organizable_type) : '') }}',
    organizerId: '{{ old('organizer_id', $currentOrganizerId) }}',
    technicalDelegate: '{{ old('technical_delegate_id', $technicalDelegate?->individual_id) }}',
    chiefJudge: '{{ old('chief_judge_id', $chiefJudge?->individual_id) }}',
    competitionDirector: '{{ old('competition_director_id', $competitionDirector?->individual_id) }}',
    technicalDelegateName: '{{ old('technical_delegate_name', $technicalDelegate?->individual?->full_name) }}',
    chiefJudgeName: '{{ old('chief_judge_name', $chiefJudge?->individual?->full_name) }}',
    competitionDirectorName: '{{ old('competition_director_name', $competitionDirector?->individual?->full_name) }}',
    checkDuplicates() {
        const values = [this.technicalDelegate, this.chiefJudge, this.competitionDirector].filter(v => v);
        const unique = [...new Set(values)];
        return values.length === unique.length;
    }
}"
x-init="
    $watch('technicalDelegate', () => { if (!checkDuplicates()) alert('{{ __('events.same_person_multiple_roles_error') }}'); });
    $watch('chiefJudge', () => { if (!checkDuplicates()) alert('{{ __('events.same_person_multiple_roles_error') }}'); });
    $watch('competitionDirector', () => { if (!checkDuplicates()) alert('{{ __('events.same_person_multiple_roles_error') }}'); });
"
x-on:individual-selected.window="
    if ($event.detail.inputId === 'technical_delegate_selector') { technicalDelegate = $event.detail.id; technicalDelegateName = $event.detail.name; }
    else if ($event.detail.inputId === 'chief_judge_selector') { chiefJudge = $event.detail.id; chiefJudgeName = $event.detail.name; }
    else if ($event.detail.inputId === 'competition_director_selector') { competitionDirector = $event.detail.id; competitionDirectorName = $event.detail.name; }
">

    {{-- ============================================== --}}
    {{-- CARD 1: Informacao do Evento --}}
    {{-- ============================================== --}}
    <div class="card">
        <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="font-semibold text-slate-700">{{ __('events.form.event_info_section') }}</span>
        </div>

        @if($category === 'competition')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Nome do Evento --}}
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="name">
                        {{ __('events.form.name') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="name"
                           class="form-input w-full @error('name') border-rose-300 @enderror"
                           placeholder="{{ __('events.form.name_placeholder') }}"
                           value="{{ old('name', $event->name ?? '') }}" required>
                    @error('name')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Modalidade --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="sport_id">
                        {{ __('events.form.sport') }} <span class="text-rose-500">*</span>
                    </label>
                    <select name="competition[sport_id]" id="sport_id" class="form-select w-full" required>
                        <option value="">{{ __('common.select') }}</option>
                        @foreach($sports as $key => $sport)
                            @php
                                $sportKey = 'sports.' . str_replace(' ', '_', strtolower($sport));
                                $sportLabel = __($sportKey) !== $sportKey ? __($sportKey) : $sport;
                            @endphp
                            <option value="{{ $key }}" @selected(old('competition.sport_id', optional($event->competition)->sport_id) == $key)>{{ $sportLabel }}</option>
                        @endforeach
                    </select>
                    @error('competition.sport_id')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Data de Inicio --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="competition_start_date">{{ __('events.form.competition_start') }} <span class="text-rose-500">*</span></label>
                    <input type="date" name="start_date" id="competition_start_date" value="{{ old('start_date', $event->start_date?->format('Y-m-d')) }}" class="form-input w-full">
                </div>

                {{-- Data de Fim --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="competition_end_date">{{ __('events.form.competition_end') }} <span class="text-rose-500">*</span></label>
                    <input type="date" name="end_date" id="competition_end_date" value="{{ old('end_date', $event->end_date?->format('Y-m-d')) }}" class="form-input w-full">
                </div>

                {{-- Estado do Evento --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="status_class">
                        {{ __('events.form.status') }} <span class="text-rose-500">*</span>
                    </label>
                    <select name="status_class" id="status_class" x-model="statusClass"
                            class="form-select w-full @error('status_class') border-rose-300 @enderror" required>
                        <option value="">{{ __('common.select') }}</option>
                        <option value="PreparationEventState" @selected(class_basename($event->status_class) == 'PreparationEventState')>{{ __('events.status.preparation') }}</option>
                        <option value="ActiveEventState" @selected(class_basename($event->status_class) == 'ActiveEventState')>{{ __('events.status.active') }}</option>
                        <option value="ArchiveEventState" @selected(class_basename($event->status_class) == 'ArchiveEventState')>{{ __('events.status.archived') }}</option>
                    </select>
                    @error('status_class')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Data de Abertura de Inscricao --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="registration_start_date">{{ __('events.form.registration_start') }}</label>
                    <input type="date" name="start_registration" id="registration_start_date" value="{{ old('start_registration', $event->start_registration?->format('Y-m-d')) }}" class="form-input w-full">
                </div>

                {{-- Data Limite de Inscricao --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="registration_end_date">{{ __('events.form.registration_end') }}</label>
                    <input type="date" name="end_registration" id="registration_end_date" value="{{ old('end_registration', $event->end_registration?->format('Y-m-d')) }}" class="form-input w-full">
                </div>

                {{-- Escalao Etario --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="cat_age">
                        {{ __('events.form.age_group') }}
                    </label>
                    <input type="text" id="cat_age" class="form-input w-full" name="competition[cat_age]"
                        value="{{ old('competition.cat_age', optional($event->competition)->cat_age) }}">
                    @error('competition.cat_age')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Numero da Competicao --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="competition_number">
                        {{ __('events.form.competition_number') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="competition[number]" id="competition_number"
                           value="{{ old('competition.number', optional($event->competition)->number) }}"
                           class="form-input w-full" required>
                    @error('competition.number')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Categoria do Evento --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="cat_competition">
                        {{ __('events.form.event_category') }}
                    </label>
                    <select id="cat_competition" name="competition[cat_competition]" class="form-select w-full">
                        <option value="">{{ __('common.select') }}</option>
                        @foreach (\App\Enums\EvtCompetitionCategoryEnum::cases() as $cat_competition)
                            <option value="{{ $cat_competition->name }}"
                                @selected(old('competition.cat_competition', optional($event->competition)->cat_competition) == $cat_competition->name)>
                                {{ $cat_competition->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('competition.cat_competition')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Ambiente --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="environment">
                        {{ __('events.form.environment') }}
                    </label>
                    <select id="environment" name="competition[environment]" class="form-select w-full">
                        @foreach (\App\Enums\EvtCompetitionEnvironmentEnum::cases() as $environment)
                            <option value="{{ $environment->name }}"
                                @selected(old('competition.environment', optional($event->competition)->environment) == $environment->name)>
                                {{ \App\Enums\EvtCompetitionEnvironmentEnum::toString($environment->value) }}
                            </option>
                        @endforeach
                    </select>
                    @error('competition.environment')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- URL Externa --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="competition_external_url">{{ __('events.form.external_url') }}</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 rounded-l border border-r-0 border-slate-200 bg-slate-50 text-slate-500 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </span>
                        <input type="url" name="external_url" id="competition_external_url"
                               class="form-input w-full rounded-l-none @error('external_url') border-rose-300 @enderror"
                               placeholder="https://example.test/event"
                               value="{{ old('external_url', $event->external_url ?? '') }}">
                    </div>
                    <p class="text-xs text-slate-500 mt-1">{{ __('events.form.external_url_hint') }}</p>
                    @error('external_url')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- URL Regulamentos --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="competition_regulations_url">{{ __('events.form.regulations_url') }}</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 rounded-l border border-r-0 border-slate-200 bg-slate-50 text-slate-500 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <input type="url" name="regulations_url" id="competition_regulations_url"
                               class="form-input w-full rounded-l-none @error('regulations_url') border-rose-300 @enderror"
                               placeholder="https://example.test/rules.pdf"
                               value="{{ old('regulations_url', $event->regulations_url ?? '') }}">
                    </div>
                    <p class="text-xs text-slate-500 mt-1">{{ __('events.form.regulations_url_hint') }}</p>
                    @error('regulations_url')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
                </div>
            </div>
        @else
            {{-- Organization Event --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Nome do Evento --}}
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="name">
                        {{ __('events.form.name') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="name"
                           class="form-input w-full @error('name') border-rose-300 @enderror"
                           placeholder="{{ __('events.form.name_placeholder') }}"
                           value="{{ old('name', $event->name ?? '') }}" required>
                    @error('name')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Tipo de Organizacao --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="organization_type">
                        {{ __('events.form.organization_type') }} <span class="text-rose-500">*</span>
                    </label>
                    <select name="organization_type" id="organization_type" class="form-select w-full" required>
                        <option value="">{{ __('common.select') }}</option>
                        @foreach(App\Enums\EvtEventOrganizationCategoryEnum::getGroupedOptions() as $group => $types)
                            <optgroup label="{{ $group }}">
                                @foreach($types as $type)
                                    <option value="{{ $type }}" @selected($event->organization_type == $type)>{{ \App\Enums\EvtEventOrganizationCategoryEnum::toString($type) }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                {{-- Estado do Evento --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="org_status_class">
                        {{ __('events.form.status') }} <span class="text-rose-500">*</span>
                    </label>
                    <select name="status_class" id="org_status_class" x-model="statusClass"
                            class="form-select w-full @error('status_class') border-rose-300 @enderror" required>
                        <option value="">{{ __('common.select') }}</option>
                        <option value="PreparationEventState" @selected(class_basename($event->status_class) == 'PreparationEventState')>{{ __('events.status.preparation') }}</option>
                        <option value="ActiveEventState" @selected(class_basename($event->status_class) == 'ActiveEventState')>{{ __('events.status.active') }}</option>
                        <option value="ArchiveEventState" @selected(class_basename($event->status_class) == 'ArchiveEventState')>{{ __('events.status.archived') }}</option>
                    </select>
                    @error('status_class')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Data de Inicio --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="start_date">{{ __('events.form.start_date') }}</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $event->start_date?->format('Y-m-d')) }}" class="form-input w-full">
                </div>

                {{-- Data de Fim --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="end_date">{{ __('events.form.end_date') }}</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $event->end_date?->format('Y-m-d')) }}" class="form-input w-full">
                </div>

                {{-- Data de Abertura de Inscricao --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="org_registration_start_date">{{ __('events.form.registration_start') }}</label>
                    <input type="date" name="start_registration" id="org_registration_start_date" value="{{ old('start_registration', $event->start_registration?->format('Y-m-d')) }}" class="form-input w-full">
                </div>

                {{-- Data Limite de Inscricao --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="org_registration_end_date">{{ __('events.form.registration_end') }}</label>
                    <input type="date" name="end_registration" id="org_registration_end_date" value="{{ old('end_registration', $event->end_registration?->format('Y-m-d')) }}" class="form-input w-full">
                </div>

                {{-- Visibility checkbox --}}
                <div class="flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_visible" id="is_visible" value="1" class="form-checkbox h-4 w-4 text-indigo-600"
                               {{ old('is_visible', $event->is_visible) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-slate-700">{{ __('events.form.visible_in_listings') }}</span>
                    </label>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
            {{-- Tipo de Inscricao --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="enrollment_type">
                    {{ __('events.form.enrollment_type') }} <span class="text-rose-500">*</span>
                </label>
                <select name="enrollment_type" id="enrollment_type"
                        class="form-select w-full @error('enrollment_type') border-rose-300 @enderror" required>
                    <option value="">{{ __('common.select') }}</option>
                    @foreach(\App\Enums\EvtEventEnrollmentTypeEnum::cases() as $type)
                        <option value="{{ $type->name }}" @selected(old('enrollment_type', $event->enrollment_type) == $type->name)>{{ __('events.form.enrollment_types.' . $type->name) }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 mt-1">{{ __('events.form.enrollment_type_hint') }}</p>
                @error('enrollment_type')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
            </div>

            {{-- Organizador --}}
            <div x-show="statusClass === 'ActiveEventState'" x-transition>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('events.form.organizer') }}</label>
                <select class="form-select w-full mb-2" x-model="organizerType" @change="organizerId = ''">
                    <option value="">{{ __('common.select') }}</option>
                    <option value="Federation">{{ __('Federation') }}</option>
                    <option value="Entity">{{ __('Entity') }}</option>
                </select>
                <div x-show="organizerType === 'Federation'" x-transition>
                    <select class="form-select w-full" x-model="organizerId">
                        <option value="">{{ __('common.select') }}</option>
                        @foreach($federations_list as $id => $name)
                            <option value="federation_{{ $id }}" @selected(old('organizer_id', $currentOrganizerId) == "federation_{$id}")>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="organizerType === 'Entity'" x-transition>
                    <select class="form-select w-full" x-model="organizerId">
                        <option value="">{{ __('common.select') }}</option>
                        @if(isset($entities_list))
                            @foreach($entities_list as $id => $name)
                                <option value="entity_{{ $id }}" @selected(old('organizer_id', $currentOrganizerId) == "entity_{$id}")>{{ $name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <input type="hidden" name="organizer_id" x-bind:value="organizerId">
            </div>

            {{-- Poster Image --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="poster">{{ __('events.form.poster') }}</label>
                @if($event->getFirstMediaUrl('poster'))
                    <div class="mb-2">
                        <img src="{{ $event->getFirstMediaUrl('poster') }}" alt="{{ __('events.form.poster') }}" class="max-h-32 rounded border border-slate-200">
                        <label class="flex items-center gap-2 mt-2 text-sm text-slate-600">
                            <input type="checkbox" name="remove_poster" value="1" class="form-checkbox">
                            {{ __('events.form.remove_poster') }}
                        </label>
                    </div>
                @endif
                <input type="file" name="poster" id="poster" accept="image/jpeg,image/png,image/webp"
                       class="form-input w-full @error('poster') border-rose-300 @enderror">
                <p class="text-xs text-slate-500 mt-1">{{ __('events.form.poster_hint') }}</p>
                @error('poster')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
            </div>

            @if($category === 'competition')
                {{-- Visibility checkbox --}}
                <div class="md:col-span-2 lg:col-span-3 flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_visible" id="is_visible" value="1" class="form-checkbox h-4 w-4 text-indigo-600"
                               {{ old('is_visible', $event->is_visible) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-slate-700">{{ __('events.form.visible_in_listings') }}</span>
                    </label>
                </div>
            @endif

            {{-- Notas / Descricao --}}
            <div class="md:col-span-2 lg:col-span-3">
                <label class="block text-sm font-medium text-slate-700 mb-1" for="editor">{{ __('events.form.notes') }}</label>
                <textarea name="notes" id="editor" rows="4"
                          class="tinymce-editor form-textarea w-full @error('notes') border-rose-300 @enderror"
                          placeholder="{{ __('events.form.notes_placeholder') }}">{{ old('notes', $event->notes ?? '') }}</textarea>
                @error('notes')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- ============================================== --}}
    {{-- CARD 2: Informacoes do Local --}}
    {{-- ============================================== --}}
    <div class="card">
        <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="font-semibold text-slate-700">{{ __('events.form.venue_section') }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Local --}}
            <div class="md:col-span-2 lg:col-span-1">
                <label class="block text-sm font-medium text-slate-700 mb-1" for="venue">{{ __('events.form.venue_name') }}</label>
                <input type="text" name="venue" id="venue" class="form-input w-full" placeholder="{{ __('events.form.venue_name_placeholder') }}" value="{{ old('venue', $event->venue) }}">
            </div>
            {{-- Morada --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1" for="venue_address">{{ __('events.form.venue_address') }}</label>
                <input type="text" name="venue_address" id="venue_address" class="form-input w-full" placeholder="{{ __('events.form.venue_address_placeholder') }}" value="{{ old('venue_address', $event->venue_address) }}">
            </div>
            {{-- Codigo Postal --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="venue_postal_code">{{ __('events.form.venue_postal_code') }}</label>
                <input type="text" name="venue_postal_code" id="venue_postal_code" class="form-input w-full" placeholder="{{ __('events.form.venue_postal_code_placeholder') }}" value="{{ old('venue_postal_code', $event->venue_postal_code) }}">
            </div>
            {{-- Cidade --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="venue_city">{{ __('events.form.venue_city') }}</label>
                <input type="text" name="venue_city" id="venue_city" class="form-input w-full" placeholder="{{ __('events.form.venue_city_placeholder') }}" value="{{ old('venue_city', $event->venue_city) }}">
            </div>
            {{-- Distrito --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="venue_district">{{ __('events.form.venue_district') }}</label>
                <select name="venue_district_id" id="venue_district" class="form-select w-full">
                    <option value="">{{ __('common.select') }}</option>
                    @if(isset($district_options))
                        @foreach($district_options as $key => $district)
                            <option value="{{ $key }}" @selected(old('venue_district_id', $event->venue_district_id) == $key)>{{ $district }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            {{-- Location URL (Google Maps) --}}
            <div class="md:col-span-2 lg:col-span-3">
                <label class="block text-sm font-medium text-slate-700 mb-1" for="location_url">{{ __('events.form.location_url') }}</label>
                <div class="flex">
                    <span class="inline-flex items-center px-3 rounded-l border border-r-0 border-slate-200 bg-slate-50 text-slate-500 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <input type="url" name="location_url" id="location_url"
                           class="form-input w-full rounded-l-none @error('location_url') border-rose-300 @enderror"
                           placeholder="https://maps.google.com/..."
                           value="{{ old('location_url', $event->location_url) }}">
                </div>
                <p class="text-xs text-slate-500 mt-1">{{ __('events.form.location_url_hint') }}</p>
                @error('location_url')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- ============================================== --}}
    {{-- CARD 4: Geografia --}}
    {{-- ============================================== --}}
    <div class="card" style="overflow: visible">
        <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="font-semibold text-slate-700">{{ __('events.form.geography_section') }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Zona --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="zone_id">{{ __('events.form.zones') }}</label>
                <livewire:input.select-multiple
                    :inputSelected="$event->zones?->pluck('id')->toArray()"
                    identifier="zones"
                    :items="$zone_options ?? []"
                    inputId="zone_id"
                    inputName="selected_zones[]" />
            </div>

            {{-- Distrito --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="district_id">{{ __('events.form.districts') }}</label>
                <livewire:input.select-multiple
                    :inputSelected="$event->districts?->pluck('id')->toArray()"
                    identifier="districts"
                    :items="$district_options ?? []"
                    inputId="district_id"
                    inputName="selected_districts[]" />
            </div>
        </div>
    </div>

    @if($category === 'competition')
        {{-- ============================================== --}}
        {{-- CARD 5: Informacoes Doping --}}
        {{-- ============================================== --}}
        <div class="card">
            <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <span class="font-semibold text-slate-700">{{ __('events.form.antidoping_section') }}</span>
            </div>

            <x-evt_event.form.competition-anti-doping-information :event="$event" :sports="$sports" :antiDoping="$anti_doping" />
        </div>

        {{-- ============================================== --}}
        {{-- CARD 6: Corpo Tecnico --}}
        {{-- ============================================== --}}
        <div class="card">
            <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span class="font-semibold text-slate-700">{{ __('events.form.management_team') }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('events.technical_delegate') }}</label>
                    <div class="flex gap-2">
                        <input type="hidden" name="technical_delegate_id" :value="technicalDelegate">
                        <div class="flex-1 p-2.5 border border-slate-200 rounded bg-slate-50 text-sm truncate" x-text="technicalDelegateName || '{{ __('events.no_individual_selected') }}'"></div>
                        <x-event-individual-selector input-id="technical_delegate_selector" />
                        <button type="button" x-show="technicalDelegate" @click="technicalDelegate = ''; technicalDelegateName = ''" class="btn btn-sm btn-outline-danger">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">{{ __('events.has_view_only_access') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('events.chief_judge') }}</label>
                    <div class="flex gap-2">
                        <input type="hidden" name="chief_judge_id" :value="chiefJudge">
                        <div class="flex-1 p-2.5 border border-slate-200 rounded bg-slate-50 text-sm truncate" x-text="chiefJudgeName || '{{ __('events.no_individual_selected') }}'"></div>
                        <x-event-individual-selector input-id="chief_judge_selector" />
                        <button type="button" x-show="chiefJudge" @click="chiefJudge = ''; chiefJudgeName = ''" class="btn btn-sm btn-outline-danger">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">{{ __('events.manages_post_event_functions') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('events.competition_director') }}</label>
                    <div class="flex gap-2">
                        <input type="hidden" name="competition_director_id" :value="competitionDirector">
                        <div class="flex-1 p-2.5 border border-slate-200 rounded bg-slate-50 text-sm truncate" x-text="competitionDirectorName || '{{ __('events.no_individual_selected') }}'"></div>
                        <x-event-individual-selector input-id="competition_director_selector" />
                        <button type="button" x-show="competitionDirector" @click="competitionDirector = ''; competitionDirectorName = ''" class="btn btn-sm btn-outline-danger">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">{{ __('events.public_facing_role') }}</p>
                </div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- CARD 7: Condicoes de Filiacao --}}
        {{-- ============================================== --}}
        <div class="card">
            <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span class="font-semibold text-slate-700">{{ __('events.form.affiliation_conditions_section') }}</span>
            </div>

            <div class="space-y-3">
                {{-- Local Federation Affiliation --}}
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox"
                           name="competition[requires_local_federation_affiliation]"
                           id="requires_local_federation_affiliation"
                           value="1"
                           class="form-checkbox h-4 w-4 text-indigo-600"
                           @checked(old('competition.requires_local_federation_affiliation', $event->competition?->requires_local_federation_affiliation ?? false))>
                    <span class="text-sm">{{ __('evt.requires_local_federation_affiliation') }}</span>
                </label>
                <p class="text-xs text-slate-500 ml-6">{{ __('evt.requires_local_federation_affiliation_hint') }}</p>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- CARD 8: Inscricao de Atletas --}}
        {{-- ============================================== --}}
        <div class="card">
            <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span class="font-semibold text-slate-700">{{ __('events.form.athlete_enrollment_section') }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Discipline Template --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="discipline_template_id">
                        {{ __('events.form.discipline_group') }}
                    </label>
                    <select id="discipline_template_id" name="competition[discipline_template_id]" class="form-select w-full">
                        <option value="">{{ __('common.select') }}</option>
                        @foreach ($discipline_templates as $template)
                            <option value="{{ $template->id }}"
                                {{ old('competition.discipline_template_id', optional($event->competition)->discipline_template_id) == $template->id ? 'selected' : '' }}>
                                {{ $template->name }}
                                @if ($template->description) - {{ $template->description }} @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500 mt-1">{{ __('events.form.discipline_group_hint') }}</p>
                </div>

                {{-- Athlete Licenses --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('events.form.athlete_licenses') }}</label>
                    <livewire:input.select-multiple :inputSelected="$event->competition?->required_athlete_licenses ?? []" identifier="athlete_licenses" :items="$licenses"
                        inputId="athlete_licenses" inputName="competition[required_athlete_licenses][]" />
                </div>
            </div>

            {{-- Entity Affiliation Requirements --}}
            <div class="mt-4 space-y-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox"
                           name="competition[requires_athlete_entity_sport_registration]"
                           id="requires_athlete_entity_sport_registration"
                           value="1"
                           class="form-checkbox h-4 w-4 text-indigo-600"
                           @checked(old('competition.requires_athlete_entity_sport_registration', $event->competition?->requires_athlete_entity_sport_registration ?? true))>
                    <span class="text-sm">{{ __('evt.requires_athlete_entity_sport_registration') }}</span>
                </label>
                <p class="text-xs text-slate-500 ml-6">{{ __('evt.requires_athlete_entity_sport_registration_hint') }}</p>
            </div>

            {{-- Enrollment Limits --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="max_disciplines_per_athlete">
                        {{ __('events.form.max_disciplines_per_athlete') }}
                    </label>
                    <input type="number" name="competition[max_disciplines_per_athlete]" id="max_disciplines_per_athlete" min="0" class="form-input w-full"
                        value="{{ old('competition.max_disciplines_per_athlete', $event->competition?->max_disciplines_per_athlete) }}">
                    <p class="text-xs text-slate-500 mt-1">{{ __('events.form.leave_empty_unlimited') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="max_relays_per_athlete">
                        {{ __('events.form.max_relays_per_athlete') }}
                    </label>
                    <input type="number" name="competition[max_relays_per_athlete]" id="max_relays_per_athlete" min="0" class="form-input w-full"
                        value="{{ old('competition.max_relays_per_athlete', $event->competition?->max_relays_per_athlete) }}">
                    <p class="text-xs text-slate-500 mt-1">{{ __('events.form.leave_empty_unlimited') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="max_teams_per_athlete">
                        {{ __('events.form.max_teams_per_athlete') }}
                    </label>
                    <input type="number" name="competition[max_teams_per_athlete]" id="max_teams_per_athlete" min="0" class="form-input w-full"
                        value="{{ old('competition.max_teams_per_athlete', $event->competition?->max_teams_per_athlete) }}">
                    <p class="text-xs text-slate-500 mt-1">{{ __('events.form.leave_empty_unlimited') }}</p>
                </div>
            </div>

            {{-- Required Athlete Documents --}}
            <div class="mt-6">
                <h4 class="text-sm font-medium text-slate-700 mb-2">{{ __('events.form.required_documents') }}</h4>
                <p class="text-xs text-slate-500 mb-3">{{ __('events.form.required_documents_hint') }}</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2">
                    @php
                        $athleteDocTypes = \App\Enums\OfficialDocumentTypeEnum::getDocumentsForEnrollmentType('athlete');
                        $selectedAthleteDocs = old('competition.required_athlete_documents', $event->competition?->required_athlete_documents ?? []);
                    @endphp
                    @foreach($athleteDocTypes as $docType)
                        <label class="flex items-center gap-2 cursor-pointer p-2 border border-slate-200 rounded hover:bg-slate-50">
                            <input type="checkbox"
                                   name="competition[required_athlete_documents][]"
                                   value="{{ $docType->value }}"
                                   class="form-checkbox h-4 w-4 text-indigo-600"
                                   @checked(in_array($docType->value, $selectedAthleteDocs))>
                            <span class="text-sm">{{ \App\Enums\OfficialDocumentTypeEnum::toString($docType) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- CARD 9: Inscricao de Treinadores --}}
        {{-- ============================================== --}}
        <div class="card">
            <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                <span class="font-semibold text-slate-700">{{ __('events.form.coach_enrollment_section') }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-evt_event.form.coach-enrollment-options :event="$event" :certifications="$certifications" />
                </div>

                <div>
                    <x-evt_event.form.coach-attributes-options :event="$event" :coachAttributes="$coach_attributes ?? collect()" />
                </div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- CARD 10: Inscricao de Oficiais de Equipa --}}
        {{-- ============================================== --}}
        <div class="card">
            <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span class="font-semibold text-slate-700">{{ __('events.form.official_enrollment_section') }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-evt_event.form.official-enrollment-options :event="$event" />
                </div>

                <div>
                    <x-evt_event.form.official-attributes-options :event="$event" :officialAttributes="$official_attributes ?? collect()" />
                </div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- CARD 11: Inscricao de Oficiais Tecnicos --}}
        {{-- ============================================== --}}
        <div class="card">
            <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-semibold text-slate-700">{{ __('events.form.referee_enrollment_section') }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    {{-- Allow Referee Enrollment --}}
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="allow_referee_enrollment" id="allow_referee_enrollment" value="1" class="form-checkbox h-4 w-4 text-indigo-600"
                            {{ old('allow_referee_enrollment', $event->allow_referee_enrollment ?? false) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-slate-700">{{ __('events.form.allow_referee_enrollment') }}</span>
                    </label>

                    {{-- Referee Certifications --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('events.form.referee_certifications') }}</label>
                        <livewire:input.select-multiple :inputSelected="$event->competition?->requiredRefereeCertifications->pluck('id')->toArray() ?? []" identifier="referee_certifications"
                            :items="$certifications" inputId="referee_certifications"
                            inputName="competition[required_referee_certifications][]" />
                    </div>

                    {{-- Required Referee Documents --}}
                    <div>
                        <h4 class="text-sm font-medium text-slate-700 mb-2">{{ __('events.form.required_documents') }}</h4>
                        <p class="text-xs text-slate-500 mb-2">{{ __('events.form.required_documents_hint') }}</p>
                        <div class="grid grid-cols-1 gap-2">
                            @php
                                $refereeDocTypes = \App\Enums\OfficialDocumentTypeEnum::getDocumentsForEnrollmentType('referee');
                                $selectedRefereeDocs = old('competition.required_referee_documents', $event->competition?->required_referee_documents ?? []);
                            @endphp
                            @foreach($refereeDocTypes as $docType)
                                <label class="flex items-center gap-2 cursor-pointer p-2 border border-slate-200 rounded hover:bg-slate-50">
                                    <input type="checkbox"
                                           name="competition[required_referee_documents][]"
                                           value="{{ $docType->value }}"
                                           class="form-checkbox h-4 w-4 text-indigo-600"
                                           @checked(in_array($docType->value, $selectedRefereeDocs))>
                                    <span class="text-sm">{{ \App\Enums\OfficialDocumentTypeEnum::toString($docType) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Referee Attributes --}}
                <div>
                    <x-evt_event.form.referee-attributes-options :event="$event" :refereeAttributes="$referee_attributes" />
                </div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- CARD 12: Premios --}}
        {{-- ============================================== --}}
        <div class="card">
            <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                <span class="font-semibold text-slate-700">{{ __('events.form.awards_section') }}</span>
            </div>

            <x-evt_event.form.competition-medals-information :event="$event" />
        </div>

    @endif

    @if($category === 'organization')
        {{-- ============================================== --}}
        {{-- CARD 5: Participant Settings (Organization) --}}
        {{-- ============================================== --}}
        <div class="card">
            <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="font-semibold text-slate-700">{{ __('events.form.participant_settings') }}</span>
            </div>

            <div>
                <x-evt_event.form.professional-selection :event="$event" :professionalRoles="$professional_roles" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <x-evt_event.form.organization-attributes-options :event="$event" :allAttributes="$member_attributes" />
                </div>
                <div>
                    <x-evt_event.form.staff-attributes-options :event="$event" :staffAttributes="$staff_attributes" />
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================== --}}
    {{-- CARD: Programa de Contabilidade --}}
    {{-- ============================================== --}}
    <div class="card">
        <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span class="font-semibold text-slate-700">{{ __('events.form.accounting_program_section') }}</span>
        </div>

        <div class="sm:w-1/3">
            <label class="block text-sm font-medium text-slate-700 mb-1" for="moloni_reference">{{ __('moloni.product_reference') }}</label>
            <input type="text" name="moloni_reference" id="moloni_reference"
                   class="form-input w-full @error('moloni_reference') border-rose-300 @enderror"
                   value="{{ old('moloni_reference', $event->moloni_reference ?? '') }}" maxlength="50">
            <p class="text-xs text-slate-500 mt-1">{{ __('moloni.product_reference_help') }}</p>
            @error('moloni_reference')<p class="text-xs mt-1 text-rose-500">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- ============================================== --}}
    {{-- FOOTER: Submit --}}
    {{-- ============================================== --}}
    <div class="card">
        <input type="hidden" name="event_category" value="{{ $category }}">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-sm text-slate-500">
                <span class="text-rose-500">*</span> {{ __('events.form.required_fields') }}
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route(Request::segment(1) . '.evt-events.events.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ isset($event->id) ? __('events.form.update_event') : __('events.form.create_event') }}
                </button>
            </div>
        </div>
    </div>

</div>
