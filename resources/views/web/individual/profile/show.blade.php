<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="flex justify-end items-start mb-5">

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-info" href="{{ route('individual.dashboard') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>

                    <span class="ml-2">{{ __('profile.back') }}</span>
                </a>
                <a class="btn btn-info" href="{{ route('individual.individual.edit') }}">
                    {{ __('profile.edit') }}
                </a>
            </div>
        </div>

        <div class="mt-16 items-start">
            <div class="col-span-8 flex flex-col flex-auto gap-y-6 mt-4 md:mt-0">
                <x-individual.profile_panel :individual="$individual" individualType="individual" />


                @if(!empty($individual->certificationsDivingAttributed))
                    <x-individual.certifications_panel :title="__('profile.diving_certifications')" committee="diving"
                                                       :individual="$individual"
                                                       :certifications="$individual->certificationsDivingAttributed"></x-individual.certifications_panel>
                @endif
                @if(!empty($individual->certificationsScientificAttributed))
                    <x-individual.certifications_panel :title="__('profile.scientific_certifications')" committee="scientific"
                                                       :individual="$individual"
                                                       :certifications="$individual->certificationsScientificAttributed"></x-individual.certifications_panel>
                @endif
                @if(!empty($individual->certificationsSportAttributed))
                    <x-individual.certifications_panel :title="__('profile.sport_certifications')" committee="sport"
                                                       :individual="$individual"
                                                       :certifications="$individual->certificationsSportAttributed"></x-individual.certifications_panel>
                @endif
                @if(!empty($individual->licensesDivingAttributed))
                    <x-individual.licenses_panel :title="__('profile.diving_licenses')" committee="diving" :individual="$individual"
                                                 :licenses="$individual->licensesDivingAttributed"></x-individual.licenses_panel>
                @endif

                @if(!empty($individual->licensesScientificAttributed))
                    <x-individual.licenses_panel :title="__('profile.scientific_licenses')" committee="scientific"
                                                 :individual="$individual"
                                                 :licenses="$individual->licensesScientificAttributed"></x-individual.licenses_panel>
                @endif

                @if(!empty($individual->licensesSportAttributed))
                    <x-individual.licenses_panel :title="__('profile.sport_licenses')" committee="sport" :individual="$individual"
                                                 :licenses="$individual->licensesSportAttributed"></x-individual.licenses_panel>
                @endif
            </div>
        </div>

    </div>
</x-layout>
