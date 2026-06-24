<div class="flex flex-col gap-y-4">
        {{-- Allow Official Enrollment --}}
        <div class="form-check">
            <input type="checkbox" name="allow_official_enrollment" id="allow_official_enrollment" value="1"
                {{ old('allow_official_enrollment', $event->allow_official_enrollment ?? true) ? 'checked' : '' }}>
            <label for="allow_official_enrollment">{{ __('events.form.allow_official_enrollment') }}</label>
            <p class="text-xs text-gray-400 mt-1">{{ __('events.form.allow_official_enrollment_hint') }}</p>
        </div>

        {{-- Required Official Documents --}}
        <div>
            <h4 class="text-sm font-medium mb-2">{{ __('events.form.required_documents') }}</h4>
            <p class="text-xs text-gray-400 mb-2">{{ __('events.form.required_documents_hint') }}</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                @php
                    $officialDocTypes = \App\Enums\OfficialDocumentTypeEnum::getDocumentsForEnrollmentType('official');
                    $selectedOfficialDocs = old('competition.required_official_documents', $event->competition?->required_official_documents ?? []);
                @endphp
                @foreach($officialDocTypes as $docType)
                    <label class="flex items-center gap-2 cursor-pointer p-2 border border-gray-200 rounded hover:bg-gray-50">
                        <input type="checkbox"
                               name="competition[required_official_documents][]"
                               value="{{ $docType->value }}"
                               class="form-checkbox h-4 w-4 text-indigo-600"
                               @checked(in_array($docType->value, $selectedOfficialDocs))>
                        <span class="text-sm">{{ \App\Enums\OfficialDocumentTypeEnum::toString($docType) }}</span>
                    </label>
                @endforeach
            </div>
        </div>
</div>
