@section('title', __('legal.terms_of_use'))
<x-public-layout>
    @php($brand = config('branding.primary'))
    <main>
        <div class="mx-auto pt-4 w-24">
            <x-brand-logo class="w-24" text-class="text-2xl font-bold text-slate-800" />
        </div>
        <div class="max-w-4xl mx-auto p-6 text-gray-700">
            <h1 class="text-3xl font-bold mb-4">{{ __('legal.terms_of_use_title') }}</h1>
            <p class="text-sm text-gray-500 mb-6">{{ __('legal.last_update') }}: 01/02/2026</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">1. {{ __('legal.terms.general_provisions') }}</h2>
            <p class="mb-4">{{ __('legal.terms.general_provisions_text') }}</p>
            <ul class="list-none mb-4 ml-4">
                <li><strong>{{ __('legal.entity') }}:</strong> {{ __('legal.federation_full_name') }}</li>
                <li><strong>{{ __('legal.address') }}:</strong> {{ $brand['address'] }}</li>
                <li><strong>{{ __('legal.email') }}:</strong> {{ $brand['support_email'] }}</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">2. {{ __('legal.terms.definitions') }}</h2>
            <ul class="list-disc ml-8 mb-4">
                <li><strong>{{ __('legal.terms.portal') }}:</strong> {{ __('legal.terms.portal_definition') }}</li>
                <li><strong>{{ __('legal.terms.user') }}:</strong> {{ __('legal.terms.user_definition') }}</li>
                <li><strong>{{ __('legal.terms.member') }}:</strong> {{ __('legal.terms.member_definition') }}</li>
                <li><strong>{{ __('legal.entity') }}:</strong> {{ __('legal.terms.entity_definition') }}</li>
                <li><strong>{{ __('legal.terms.services') }}:</strong> {{ __('legal.terms.services_definition') }}</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">3. {{ __('legal.terms.acceptance') }}</h2>
            <p class="mb-4">{{ __('legal.terms.acceptance_text') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">4. {{ __('legal.terms.services_description') }}</h2>
            <p class="mb-2">{{ __('legal.terms.services_description_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.terms.service_profile_management') }}</li>
                <li>{{ __('legal.terms.service_license_acquisition') }}</li>
                <li>{{ __('legal.terms.service_certification_management') }}</li>
                <li>{{ __('legal.terms.service_event_registration') }}</li>
                <li>{{ __('legal.terms.service_document_access') }}</li>
                <li>{{ __('legal.terms.service_payment_processing') }}</li>
                <li>{{ __('legal.terms.service_insurance_management') }}</li>
                <li>{{ __('legal.terms.service_institutional_info') }}</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">5. {{ __('legal.terms.user_registration') }}</h2>
            <p class="mb-2">{{ __('legal.terms.user_registration_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.terms.registration_true_info') }}</li>
                <li>{{ __('legal.terms.registration_keep_updated') }}</li>
                <li>{{ __('legal.terms.registration_credentials') }}</li>
                <li>{{ __('legal.terms.registration_notify') }}</li>
            </ul>

            <h3 class="text-xl font-semibold mt-4 mb-2">5.1. {{ __('legal.terms.public_disclosure') }}</h3>
            <p class="mb-2">{{ __('legal.terms.public_disclosure_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.terms.public_disclosure_photo') }}</li>
                <li>{{ __('legal.terms.public_disclosure_name') }}</li>
                <li>{{ __('legal.terms.public_disclosure_birth_date') }}</li>
                <li>{{ __('legal.terms.public_disclosure_entity') }}</li>
                <li>{{ __('legal.terms.public_disclosure_license_status') }}</li>
            </ul>
            <p class="mb-2">{{ __('legal.terms.public_disclosure_mandatory') }}</p>
            <p class="mb-4">{{ __('legal.terms.public_disclosure_purpose') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">6. {{ __('legal.terms.user_obligations') }}</h2>
            <p class="mb-2">{{ __('legal.terms.user_obligations_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.terms.obligation_lawful_use') }}</li>
                <li>{{ __('legal.terms.obligation_true_info') }}</li>
                <li>{{ __('legal.terms.obligation_respect_ip') }}</li>
                <li>{{ __('legal.terms.obligation_security') }}</li>
                <li>{{ __('legal.terms.obligation_no_illegal') }}</li>
                <li>{{ __('legal.terms.obligation_no_harmful') }}</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">7. {{ __('legal.terms.prohibited_conduct') }}</h2>
            <p class="mb-2">{{ __('legal.terms.prohibited_conduct_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.terms.prohibited_unauthorized_access') }}</li>
                <li>{{ __('legal.terms.prohibited_malware') }}</li>
                <li>{{ __('legal.terms.prohibited_interference') }}</li>
                <li>{{ __('legal.terms.prohibited_bots') }}</li>
                <li>{{ __('legal.terms.prohibited_impersonation') }}</li>
                <li>{{ __('legal.terms.prohibited_illegal_activities') }}</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">8. {{ __('legal.terms.intellectual_property') }}</h2>
            <p class="mb-4">{{ __('legal.terms.intellectual_property_text') }}</p>
            <p class="mb-4">{{ __('legal.terms.intellectual_property_license') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">9. {{ __('legal.terms.payments') }}</h2>
            <p class="mb-2">{{ __('legal.terms.payments_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.terms.payments_prices') }}</li>
                <li>{{ __('legal.terms.payments_methods') }}</li>
                <li>{{ __('legal.terms.payments_confirmation') }}</li>
                <li>{{ __('legal.terms.payments_refunds') }}</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">10. {{ __('legal.terms.liability_limitation') }}</h2>
            <p class="mb-2">{{ __('legal.terms.liability_limitation_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.terms.liability_interruptions') }}</li>
                <li>{{ __('legal.terms.liability_errors') }}</li>
                <li>{{ __('legal.terms.liability_third_party') }}</li>
                <li>{{ __('legal.terms.liability_force_majeure') }}</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">11. {{ __('legal.terms.warranty_exclusion') }}</h2>
            <p class="mb-4">{{ __('legal.terms.warranty_exclusion_text') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">12. {{ __('legal.terms.indemnification') }}</h2>
            <p class="mb-4">{{ __('legal.terms.indemnification_text') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">13. {{ __('legal.terms.third_party_links') }}</h2>
            <p class="mb-4">{{ __('legal.terms.third_party_links_text') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">14. {{ __('legal.terms.suspension_termination') }}</h2>
            <p class="mb-2">{{ __('legal.terms.suspension_termination_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.terms.suspension_terms_violation') }}</li>
                <li>{{ __('legal.terms.suspension_illegal_acts') }}</li>
                <li>{{ __('legal.terms.suspension_harmful_conduct') }}</li>
                <li>{{ __('legal.terms.suspension_user_request') }}</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">15. {{ __('legal.terms.terms_changes') }}</h2>
            <p class="mb-4">{{ __('legal.terms.terms_changes_text') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">16. {{ __('legal.terms.applicable_law') }}</h2>
            <p class="mb-4">{{ __('legal.terms.applicable_law_text') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">17. {{ __('legal.terms.dispute_resolution') }}</h2>
            <p class="mb-4">{{ __('legal.terms.dispute_resolution_text') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">18. {{ __('legal.terms.severability') }}</h2>
            <p class="mb-4">{{ __('legal.terms.severability_text') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">19. {{ __('legal.contacts') }}</h2>
            <p class="mb-2">{{ __('legal.terms.contacts_intro') }}</p>
            <ul class="list-none mb-4 ml-4">
                <li><strong>{{ __('legal.email') }}:</strong> {{ $brand['support_email'] }}</li>
                <li><strong>{{ __('legal.address') }}:</strong> {{ $brand['address'] }}</li>
            </ul>
            <p class="mb-4">{{ __('legal.terms.privacy_policy_reference') }}</p>
        </div>
    </main>
</x-public-layout>
