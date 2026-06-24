<div class="flex flex-col gap-y-4">
        {{-- Allow Coach Enrollment --}}
        <div class="form-check">
            <input type="checkbox" name="allow_coach_enrollment" id="allow_coach_enrollment_section" value="1"
                {{ old('allow_coach_enrollment', $event->allow_coach_enrollment ?? false) ? 'checked' : '' }}>
            <label for="allow_coach_enrollment_section">{{ __('events.form.allow_coach_enrollment') }}</label>
            <p class="text-xs text-gray-400 mt-1">{{ __('events.form.allow_coach_enrollment_hint') }}</p>
        </div>

        {{-- Coach Entity Sport Registration --}}
        <div class="form-check">
            <input type="checkbox"
                   name="competition[requires_coach_entity_sport_registration]"
                   id="requires_coach_entity_sport_registration"
                   value="1"
                   {{ old('competition.requires_coach_entity_sport_registration', $event->competition?->requires_coach_entity_sport_registration ?? true) ? 'checked' : '' }}>
            <label for="requires_coach_entity_sport_registration">{{ __('evt.requires_coach_entity_sport_registration') }}</label>
            <p class="text-xs text-gray-400 mt-1">{{ __('evt.requires_coach_entity_sport_registration_hint') }}</p>
        </div>

        {{-- Coach Certifications --}}
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('events.form.coach_certifications') }}</label>
            <livewire:input.select-multiple
                :inputSelected="$event->competition?->requiredCoachCertifications->pluck('id')->toArray() ?? []"
                identifier="coach_certifications_section"
                :items="$certifications ?? []"
                inputId="coach_certifications_section"
                inputName="competition[required_coach_certifications][]" />
            <p class="text-xs text-gray-400 mt-1">{{ __('events.form.coach_certifications_hint') }}</p>
        </div>

        {{-- Required Coach Documents --}}
        <div>
            <h4 class="text-sm font-medium mb-2">{{ __('events.form.required_documents') }}</h4>
            <p class="text-xs text-gray-400 mb-2">{{ __('events.form.required_documents_hint') }}</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                @php
                    $coachDocTypes = \App\Enums\OfficialDocumentTypeEnum::getDocumentsForEnrollmentType('coach');
                    $selectedCoachDocs = old('competition.required_coach_documents', $event->competition?->required_coach_documents ?? []);
                @endphp
                @foreach($coachDocTypes as $docType)
                    <label class="flex items-center gap-2 cursor-pointer p-2 border border-gray-200 rounded hover:bg-gray-50">
                        <input type="checkbox"
                               name="competition[required_coach_documents][]"
                               value="{{ $docType->value }}"
                               class="form-checkbox h-4 w-4 text-indigo-600"
                               @checked(in_array($docType->value, $selectedCoachDocs))>
                        <span class="text-sm">{{ \App\Enums\OfficialDocumentTypeEnum::toString($docType) }}</span>
                    </label>
                @endforeach
            </div>
        </div>
</div>
