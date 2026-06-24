@section('title', 'Assign Certification')
<x-layout>
    <x-certification_attributed.form_create
        :federations="null"
        :isFederation="true"
        :isAdmin="false"
        :federationId="$federationId"
        :entityId="null" />
</x-layout>
