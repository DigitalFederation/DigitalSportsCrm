<x-layout>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-4"> {{ __('Upload Document for Member') }}</h1>

        <livewire:official-documents-federation-upload
            :individuals="$individuals"
            :types="$documentTypes"
            :federations="collect([$federation])"
            :role="null"
            :isForIndividual="true"
        />
    </div>
</x-layout>
