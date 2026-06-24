<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title">
                @if(!empty($professional_role))
                    @if($professional_role == 'INSTRUCTORLEADER')
                        {{ __('Instructor & Leaders') }}
                    @elseif($professional_role == 'REFEREEJUDGE')
                        {{ __('Referees & Judges') }}
                    @else
                        {{ ucwords(strtolower($professional_role)) }}
                    @endif
                @else
                    {{ ucwords($license_type_name) }}
                @endif
                {{ __('license') }}
            </h1>
        </div>

        @php
            $instructions_string_step_1 = __('Select the license you want to attribute.');
            $instructions_string_step_2 = __('Add the individuals to be licensed by entering each international code and clicking Insert.');
            $instructions_string_step_3 = __('Click on Save request to submit the request. A document will be generated after submission.');
            $instructions_complete = '<strong>1. </strong>'.$instructions_string_step_1 . '<br><strong>2. </strong>' . $instructions_string_step_2 . '<br><strong>3. </strong>' . $instructions_string_step_3;
        @endphp
        <x-information-box
            title="{{ __('Instructions to request a license') }}"
            :body="$instructions_complete"
        ></x-information-box>


        <x-common.license_attributed.create
            :committee="$committee"
            :federations="null"
            :federation="$federation->id"
            :licenseTypeName="$license_type_name"
            :entities="$entities"
            :entity="null"
            :requesterModelType="$requester_model_type"
            :professionalRole="$professional_role" />

    </div>

</x-layout>
