@section('title', __('legal.privacy_policy'))
<x-public-layout>
    @php($brand = config('branding.primary'))
    <main>
        <div class="mx-auto pt-4 w-24">
            <x-brand-logo class="w-24" text-class="text-2xl font-bold text-slate-800" />
        </div>
        <div class="max-w-4xl mx-auto p-6 text-gray-700">
            <h1 class="text-3xl font-bold mb-4">{{ __('legal.privacy_policy_title') }}</h1>
            <p class="text-sm text-gray-500 mb-6">{{ __('legal.last_update') }}: 01/02/2026</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">1. {{ __('legal.privacy.responsible_entity') }}</h2>
            <p class="mb-4">
                {{ __('legal.privacy.responsible_entity_text') }}
            </p>
            <ul class="list-none mb-4 ml-4">
                <li><strong>{{ __('legal.entity') }}:</strong> {{ __('legal.federation_full_name') }}</li>
                <li><strong>{{ __('legal.address') }}:</strong> {{ $brand['address'] }}</li>
                <li><strong>{{ __('legal.email') }}:</strong> {{ $brand['support_email'] }}</li>
                <li><strong>{{ __('legal.privacy.dpo') }}:</strong> {{ __('legal.privacy.dpo_department') }}</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">2. {{ __('legal.privacy.legal_framework') }}</h2>
            <p class="mb-2">{{ __('legal.privacy.legal_framework_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.privacy.gdpr_reference') }}</li>
                <li>{{ __('legal.privacy.law_58_2019') }}</li>
                <li>{{ __('legal.privacy.law_41_2004') }}</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">3. {{ __('legal.privacy.collected_data') }}</h2>
            <p class="mb-2">{{ __('legal.privacy.collected_data_intro') }}</p>

            <h3 class="text-xl font-semibold mt-4 mb-2">3.1. {{ __('legal.privacy.identification_data') }}</h3>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.privacy.full_name') }}</li>
                <li>{{ __('legal.privacy.birth_date') }}</li>
                <li>{{ __('legal.privacy.gender') }}</li>
                <li>{{ __('legal.privacy.nationality') }}</li>
                <li>{{ __('legal.privacy.tax_number') }}</li>
                <li>{{ __('legal.privacy.id_document') }}</li>
                <li>{{ __('legal.privacy.photo') }}</li>
            </ul>

            <h3 class="text-xl font-semibold mt-4 mb-2">3.2. {{ __('legal.privacy.contact_data') }}</h3>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.privacy.full_address') }}</li>
                <li>{{ __('legal.privacy.email_address') }}</li>
                <li>{{ __('legal.privacy.phone_number') }}</li>
            </ul>

            <h3 class="text-xl font-semibold mt-4 mb-2">3.3. {{ __('legal.privacy.sports_data') }}</h3>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.privacy.certifications_brevets') }}</li>
                <li>{{ __('legal.privacy.federative_licenses') }}</li>
                <li>{{ __('legal.privacy.entity_affiliations') }}</li>
                <li>{{ __('legal.privacy.event_participation') }}</li>
                <li>{{ __('legal.privacy.sports_results') }}</li>
            </ul>

            <h3 class="text-xl font-semibold mt-4 mb-2">3.4. {{ __('legal.privacy.health_data') }}</h3>
            <p class="mb-4">{{ __('legal.privacy.health_data_text') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">4. {{ __('legal.privacy.processing_purposes') }}</h2>
            <p class="mb-2">{{ __('legal.privacy.processing_purposes_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.privacy.purpose_member_management') }}</li>
                <li>{{ __('legal.privacy.purpose_license_management') }}</li>
                <li>{{ __('legal.privacy.purpose_certification_management') }}</li>
                <li>{{ __('legal.privacy.purpose_event_management') }}</li>
                <li>{{ __('legal.privacy.purpose_insurance_management') }}</li>
                <li>{{ __('legal.privacy.purpose_institutional_communication') }}</li>
                <li>{{ __('legal.privacy.purpose_legal_obligations') }}</li>
                <li>{{ __('legal.privacy.purpose_statistics') }}</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">5. {{ __('legal.privacy.legal_basis') }}</h2>
            <p class="mb-2">{{ __('legal.privacy.legal_basis_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li><strong>{{ __('legal.privacy.consent') }}:</strong> {{ __('legal.privacy.consent_text') }}</li>
                <li><strong>{{ __('legal.privacy.contract_execution') }}:</strong> {{ __('legal.privacy.contract_execution_text') }}</li>
                <li><strong>{{ __('legal.privacy.legal_obligation') }}:</strong> {{ __('legal.privacy.legal_obligation_text') }}</li>
                <li><strong>{{ __('legal.privacy.legitimate_interest') }}:</strong> {{ __('legal.privacy.legitimate_interest_text') }}</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">6. {{ __('legal.privacy.data_sharing') }}</h2>
            <p class="mb-2">{{ __('legal.privacy.data_sharing_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li><strong>{{ __('legal.privacy.cmas') }}</strong> - {{ __('legal.privacy.cmas_reason') }}</li>
                <li><strong>{{ __('legal.privacy.public_sports_authority') }}</strong> - {{ __('legal.privacy.public_sports_authority_reason') }}</li>
                <li><strong>{{ __('legal.privacy.cop') }}</strong> - {{ __('legal.privacy.cop_reason') }}</li>
                <li><strong>{{ __('legal.privacy.insurers') }}</strong> - {{ __('legal.privacy.insurers_reason') }}</li>
                <li><strong>{{ __('legal.privacy.affiliated_entities') }}</strong> - {{ __('legal.privacy.affiliated_entities_reason') }}</li>
                <li><strong>{{ __('legal.privacy.public_authorities') }}</strong> - {{ __('legal.privacy.public_authorities_reason') }}</li>
            </ul>
            <p class="mb-4">{{ __('legal.privacy.data_sharing_compliance') }}</p>

            <h3 class="text-xl font-semibold mt-4 mb-2">6.1. {{ __('legal.privacy.public_disclosure') }}</h3>
            <p class="mb-2">{{ __('legal.privacy.public_disclosure_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.privacy.public_disclosure_photo') }}</li>
                <li>{{ __('legal.privacy.public_disclosure_name') }}</li>
                <li>{{ __('legal.privacy.public_disclosure_birth_date') }}</li>
                <li>{{ __('legal.privacy.public_disclosure_entity') }}</li>
                <li>{{ __('legal.privacy.public_disclosure_license_status') }}</li>
            </ul>
            <p class="mb-2">{{ __('legal.privacy.public_disclosure_mandatory') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li>{{ __('legal.privacy.public_disclosure_contract') }}</li>
                <li>{{ __('legal.privacy.public_disclosure_legal_obligation') }}</li>
                <li>{{ __('legal.privacy.public_disclosure_legitimate_interest') }}</li>
            </ul>
            <p class="mb-4">{{ __('legal.privacy.public_disclosure_no_removal') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">7. {{ __('legal.privacy.international_transfers') }}</h2>
            <p class="mb-4">{{ __('legal.privacy.international_transfers_text') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">8. {{ __('legal.privacy.retention_period') }}</h2>
            <p class="mb-2">{{ __('legal.privacy.retention_period_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li><strong>{{ __('legal.privacy.active_member_data') }}:</strong> {{ __('legal.privacy.active_member_data_text') }}</li>
                <li><strong>{{ __('legal.privacy.legal_obligation_data') }}:</strong> {{ __('legal.privacy.legal_obligation_data_text') }}</li>
                <li><strong>{{ __('legal.privacy.financial_data') }}:</strong> {{ __('legal.privacy.financial_data_text') }}</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">9. {{ __('legal.privacy.data_subject_rights') }}</h2>
            <p class="mb-2">{{ __('legal.privacy.data_subject_rights_intro') }}</p>
            <ul class="list-disc ml-8 mb-4">
                <li><strong>{{ __('legal.privacy.right_access') }}:</strong> {{ __('legal.privacy.right_access_text') }}</li>
                <li><strong>{{ __('legal.privacy.right_rectification') }}:</strong> {{ __('legal.privacy.right_rectification_text') }}</li>
                <li><strong>{{ __('legal.privacy.right_erasure') }}:</strong> {{ __('legal.privacy.right_erasure_text') }}</li>
                <li><strong>{{ __('legal.privacy.right_portability') }}:</strong> {{ __('legal.privacy.right_portability_text') }}</li>
                <li><strong>{{ __('legal.privacy.right_objection') }}:</strong> {{ __('legal.privacy.right_objection_text') }}</li>
                <li><strong>{{ __('legal.privacy.right_restriction') }}:</strong> {{ __('legal.privacy.right_restriction_text') }}</li>
                <li><strong>{{ __('legal.privacy.right_withdraw_consent') }}:</strong> {{ __('legal.privacy.right_withdraw_consent_text') }}</li>
            </ul>
            <p class="mb-4">{{ __('legal.privacy.exercise_rights_text') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">10. {{ __('legal.privacy.data_security') }}</h2>
            <p class="mb-4">{{ __('legal.privacy.data_security_text') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">11. {{ __('legal.privacy.cookies') }}</h2>
            <p class="mb-4">{{ __('legal.privacy.cookies_text') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">12. {{ __('legal.privacy.complaints') }}</h2>
            <p class="mb-2">{{ __('legal.privacy.complaints_intro') }}</p>
            <ul class="list-none mb-4 ml-4">
                <li><strong>{{ __('legal.privacy.cnpd') }}</strong></li>
                <li>Av. D. Carlos I, 134 - 1., 1200-651 Lisboa</li>
                <li>Website: www.cnpd.pt</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">13. {{ __('legal.privacy.policy_changes') }}</h2>
            <p class="mb-4">{{ __('legal.privacy.policy_changes_text') }}</p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">14. {{ __('legal.contacts') }}</h2>
            <p class="mb-2">{{ __('legal.privacy.contacts_intro') }}</p>
            <ul class="list-none mb-4 ml-4">
                <li><strong>{{ __('legal.email') }}:</strong> {{ $brand['support_email'] }}</li>
                <li><strong>{{ __('legal.address') }}:</strong> {{ $brand['address'] }}</li>
            </ul>
        </div>
    </main>
</x-public-layout>
