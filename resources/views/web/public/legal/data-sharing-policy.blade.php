@section('title', 'Data Sharing Policy')
<x-public-layout>
    @php($brand = config('branding.primary'))
    <main>
        <div class="mx-auto pt-4 w-24">
            <img src="{{ asset($brand['logo_path']) }}" class="w-24" alt="{{ $brand['short_name'] }}">
        </div>

        <div class="max-w-4xl mx-auto p-6 text-gray-700">
            <h1 class="text-3xl font-bold mb-4">DATA SHARING POLICY</h1>
            <p class="text-sm text-gray-500 mb-6">{{ __('legal.last_update') }}: 01/02/2026</p>

            <p class="mb-4">
                This sample policy describes the types of data sharing that may occur when a federation uses
                {{ $brand['portal_name'] }}. Public deployments must replace this text with legal terms reviewed
                for their own jurisdiction, federation rules, and processing activities.
            </p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">1. Responsible Entity</h2>
            <p class="mb-4">
                The responsible entity for a deployment is the organization operating that deployment.
                For this installation, the configured entity is {{ $brand['name'] }}.
            </p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">2. Data Recipients</h2>
            <p class="mb-2">Depending on enabled modules, data may be shared with:</p>
            <ul class="list-disc ml-8 mb-4">
                <li>Affiliated entities, clubs, schools, or service providers involved in member management.</li>
                <li>Payment processors for transaction handling.</li>
                <li>Insurance providers when insurance products are requested.</li>
                <li>Certification or license bodies when issuing credentials.</li>
                <li>Public authorities when required by applicable law.</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-6 mb-2">3. Public Directories</h2>
            <p class="mb-4">
                Public directories may expose selected profile, license, certification, entity, or event data.
                Each operator must configure publication rules according to consent, legitimate interest,
                contractual obligations, and local law.
            </p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">4. International Transfers</h2>
            <p class="mb-4">
                If data is transferred outside the operator's jurisdiction, the operator is responsible for
                documenting the transfer mechanism and any required safeguards.
            </p>

            <h2 class="text-2xl font-semibold mt-6 mb-2">5. Contact</h2>
            <ul class="list-none mb-4 ml-4">
                <li><strong>{{ __('legal.email') }}:</strong> {{ $brand['support_email'] }}</li>
                <li><strong>{{ __('legal.address') }}:</strong> {{ $brand['address'] }}</li>
            </ul>
        </div>
    </main>
</x-public-layout>
