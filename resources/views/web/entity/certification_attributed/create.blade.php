@section('title', 'Certification Request')
<x-layout>
    <x-certification_attributed.form_create
        :federations="null"
        :entities="null"
        :isFederation="false"
        :isAdmin="false"
        :isEntity="true"
        :federationId="$federationId"
        :entityId="$entityId" />
</x-layout>
