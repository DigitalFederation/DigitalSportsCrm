<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert empty strings to null for nullable integer fields
        $this->merge([
            'technical_delegate_id' => $this->technical_delegate_id ?: null,
            'chief_judge_id' => $this->chief_judge_id ?: null,
            'competition_director_id' => $this->competition_director_id ?: null,
            'venue_country_id' => $this->venue_country_id ?: null,
            'venue_district_id' => $this->venue_district_id ?: null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'location' => 'nullable|string',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'event_category' => 'required|string',
            'event_type' => 'nullable|string',
            'organization_type' => 'nullable|string',
            'status_class' => 'required|string',
            'is_visible' => 'nullable|boolean',
            'enrollment_type' => 'required|string',
            'external_url' => 'nullable|url|max:500',
            'regulations_url' => 'nullable|url|max:500',
            'start_registration' => 'nullable|date|before_or_equal:end_registration',
            'end_registration' => 'nullable|date|after_or_equal:start_registration',
            'selected_attributes' => 'nullable|array',
            'selected_attributes.*' => 'exists:evt_attributes,id',
            'selected_staff_attributes' => 'nullable|array',
            'selected_staff_attributes.*' => 'exists:evt_attributes,id',
            'selected_referee_attributes' => 'nullable|array',
            'selected_referee_attributes.*' => 'exists:evt_attributes,id',
            'selected_countries' => 'nullable|array',
            'selected_geo_zones' => 'nullable|array',
            'selected_zones' => 'nullable|array',
            'selected_zones.*' => 'exists:zones,id',
            'selected_districts' => 'nullable|array',
            'selected_districts.*' => 'exists:districts,id',
            'professional_roles' => 'nullable|array',
            'professional_roles.*' => 'exists:professional_roles,id',
            'venue' => 'nullable|string',
            'venue_address' => 'nullable|string',
            'venue_postal_code' => 'nullable|string|max:20',
            'venue_city' => 'nullable|string',
            'venue_country_id' => 'nullable|integer|exists:country,id',
            'venue_district_id' => 'nullable|integer|exists:districts,id',
            'location_url' => 'nullable|url|max:500',
            'poster' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'moloni_reference' => 'nullable|string|max:50',
            'remove_poster' => 'nullable|boolean',
            'organizer_id' => ['nullable', 'string', 'regex:/^(federation|entity)_\d+$/'],
            'organizer_details.bod_meeting_no' => 'nullable|string',
            'organizer_details.date_sending_contract' => 'nullable|date',
            'organizer_details.date_sending_invoice' => 'nullable|date',
            'organizer_details.date_reception_payment' => 'nullable|date',
            'organizer_details.date_reception_contract_signed' => 'nullable|date',
            'organizer_details.date_reception_specific_rules' => 'nullable|date',
            'organizer_details.responsible_person' => 'nullable|string',
            'organizer_details.email_contact' => 'nullable|email',
            'organizer_details.phone_contact' => 'nullable|string',
            'allow_coach_enrollment' => 'nullable|boolean',
            'allow_referee_enrollment' => 'nullable|boolean',
            'allow_official_enrollment' => 'nullable|boolean',
            'allow_individual_enrollment' => 'nullable|boolean',
            'selected_coach_attributes' => 'nullable|array',
            'selected_coach_attributes.*' => 'exists:evt_attributes,id',
            'selected_official_attributes' => 'nullable|array',
            'selected_official_attributes.*' => 'exists:evt_attributes,id',
            'public_athlete_list' => 'nullable|boolean',
            'public_coach_list' => 'nullable|boolean',
            'public_referee_list' => 'nullable|boolean',
            'technical_delegate_id' => 'nullable|exists:individual,id',
            'chief_judge_id' => 'nullable|exists:individual,id',
            'competition_director_id' => 'nullable|exists:individual,id',
        ];

        // Add conditional rules similar to StoreEventRequest
        if ($this->input('event_category') === 'competition') {
            $rules += [
                'competition.status_class' => 'nullable|string',
                'competition.number' => 'nullable|string',
                'competition.sport_id' => 'required|integer',
                'competition.competition_start_date' => 'nullable|date|before_or_equal:competition.competition_end_date',
                'competition.competition_end_date' => 'nullable|date|after_or_equal:competition.competition_start_date',
                'competition.rounds_total' => 'nullable|integer',
                'competition.cat_age' => 'nullable|string',
                'competition.cat_competition' => 'nullable|string',
                'competition.types' => 'nullable|array',
                'competition.environment' => 'nullable|string',
                'competition.medals_gold' => 'nullable|integer',
                'competition.medals_silver' => 'nullable|integer',
                'competition.medals_bronze' => 'nullable|integer',
                'competition.trophies_first' => 'nullable|integer|min:0',
                'competition.trophies_second' => 'nullable|integer|min:0',
                'competition.trophies_third' => 'nullable|integer|min:0',
                'competition.discipline_template_id' => 'nullable|exists:evt_discipline_templates,id',
                'anti_doping.responsible_name' => 'nullable|string',
                'anti_doping.responsible_phone' => 'nullable|string',
                'anti_doping.responsible_email' => 'nullable|email',
                'anti_doping.num_controls_planned' => 'nullable|integer',
                'anti_doping.number_of_controls' => 'nullable|integer',
                'anti_doping.expected_athletes' => 'nullable|integer',
                'technical_delegate.name' => 'nullable|string',
                'technical_delegate.federation_id' => 'nullable|integer|exists:federation,id',
                'technical_delegate.member_code_delegate_federation' => 'required_with:technical_delegate.name|nullable|string',
                'technical_delegate.appointment_by_bod_number' => 'nullable|string',
                'technical_delegate.date_of_bod_appointment' => 'nullable|date',
                'competition.required_athlete_licenses' => 'nullable|array',
                'competition.required_athlete_licenses.*' => 'exists:license,id',
                'competition.required_coach_certifications' => 'nullable|array',
                'competition.required_coach_certifications.*' => 'exists:certification,id',
                'competition.required_referee_certifications' => 'nullable|array',
                'competition.required_referee_certifications.*' => 'exists:certification,id',
                'competition.requires_athlete_adel' => 'boolean',
                'competition.requires_coach_adel' => 'boolean',
                'competition.requires_referee_adel' => 'boolean',
                'competition.requires_official_adel' => 'boolean',
                'competition.requires_local_federation_affiliation' => 'boolean',
                'competition.requires_athlete_entity_sport_registration' => 'boolean',
                'competition.requires_coach_entity_sport_registration' => 'boolean',
                'competition.required_athlete_documents' => 'nullable|array',
                'competition.required_athlete_documents.*' => 'string',
                'competition.required_coach_documents' => 'nullable|array',
                'competition.required_coach_documents.*' => 'string',
                'competition.required_referee_documents' => 'nullable|array',
                'competition.required_referee_documents.*' => 'string',
                'competition.required_official_documents' => 'nullable|array',
                'competition.required_official_documents.*' => 'string',
                'competition.max_disciplines_per_athlete' => 'nullable|integer|min:0',
                'competition.max_relays_per_athlete' => 'nullable|integer|min:0',
                'competition.max_teams_per_athlete' => 'nullable|integer|min:0',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The event name is required.',
            'name.string' => 'The event name must be a string.',
            'name.max' => 'The event name may not be greater than 255 characters.',
            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'The start date is not a valid date.',
            'start_date.before_or_equal' => 'The start date must be before or equal to the end date.',
            'end_date.required' => 'The end date is required.',
            'end_date.date' => 'The end date is not a valid date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'event_category.required' => 'The event category is required.',
            'status_class.required' => 'The status class is required.',
            'enrollment_type.required' => 'The enrollment type is required.',
            'professional_roles.*.exists' => 'The selected professional role is invalid.',
            'venue_country_id.exists' => 'The selected venue country is invalid.',
            'organizer_id.regex' => 'The selected organizer format is invalid.',

            'technical_delegate.name.required' => 'The delegate name is required.',
            'technical_delegate.federation_id.required' => 'Please select a federation for the technical delegate.',
            'technical_delegate.federation_id.exists' => 'The selected federation does not exist.',
            'technical_delegate.member_code_delegate_federation.required' => 'The CMAS delegate code is required.',
            'technical_delegate.member_code_delegate_federation.required_with' => 'The CMAS delegate code is required when the technical delegate name is provided.',
            'technical_delegate.member_code_delegate_federation.exists' => 'The CMAS delegate code must exist in the records.',
            'technical_delegate.appointment_by_bod_number.required' => 'The appointment by BOD number is required.',
            'technical_delegate.date_of_bod_appointment.required' => 'The date of BOD appointment is required.',
            'technical_delegate.date_of_bod_appointment.date' => 'The date of BOD appointment is not a valid date.',

            'competition.max_disciplines_per_athlete.integer' => 'The maximum disciplines per athlete must be a number.',
            'competition.max_disciplines_per_athlete.min' => 'The maximum disciplines per athlete must be at least 0.',
            'competition.max_relays_per_athlete.integer' => 'The maximum relays per athlete must be a number.',
            'competition.max_relays_per_athlete.min' => 'The maximum relays per athlete must be at least 0.',
            'competition.max_teams_per_athlete.integer' => 'The maximum teams per athlete must be a number.',
            'competition.max_teams_per_athlete.min' => 'The maximum teams per athlete must be at least 0.',

            // Certification and license selection validation
            'competition.required_referee_certifications.*.exists' => __('evt.validation.required_referee_certifications_invalid'),
            'competition.required_coach_certifications.*.exists' => __('evt.validation.required_coach_certifications_invalid'),
            'competition.required_athlete_licenses.*.exists' => __('evt.validation.required_athlete_licenses_invalid'),

            // Document selection validation
            'competition.required_athlete_documents.*.string' => __('evt.validation.required_athlete_documents_invalid'),
            'competition.required_coach_documents.*.string' => __('evt.validation.required_coach_documents_invalid'),
            'competition.required_referee_documents.*.string' => __('evt.validation.required_referee_documents_invalid'),
            'competition.required_official_documents.*.string' => __('evt.validation.required_official_documents_invalid'),
        ];
    }
}
