<x-layout>
    <section class="previous-layout-classes">

        <div class="flex flex-col md:flex-row justify-between items-center mb-4">

            <h2 class="text-left text-lg md:text-2xl font-bold">
                {{ $certification_attributed->certification_name }}
            </h2>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('individual.certification-card.index') }}" class="btn btn-info">
                    {{ __('Back') }}
                </a>

                <livewire:generate-individual-certification-card-pdf
                    :certificationAttributedId="$certification_attributed->id" />

            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-x-4">
            <div class="w-full md:w-auto">

                <div class="mb-4 w-full md:w-96 md:h-60">
                    <x-certification_attributed.card_front :certificationAttributed="$certification_attributed" />
                </div>

                <x-certification_attributed.card_reverse :certificationAttributed="$certification_attributed" />

            </div>

            <!--
            <div class="w-full md:w-auto">
                <div class="w-full">
                    <x-certification_attributed.organization_card :certificationAttributed="$certification_attributed"/>
                    <x-certification_attributed.entity_card :certificationAttributed="$certification_attributed"/>
                </div>
            </div>
            -->

        </div>

    </section>
</x-layout>
