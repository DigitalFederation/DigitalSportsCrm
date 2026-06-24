<x-layout>
    <x-certification.layout
        :certification="$certification"
        userType="ENTITY"
    >
        <x-slot name="headerActions">
            <div class="flex items-center space-x-4"></div>
        </x-slot>

        <x-slot name="mainContent">
            {{-- Main certification details --}}
            <x-certification.details :certification="$certification" />

            {{-- Organization information --}}
            <x-certification.organization-info :certification="$certification" />

            {{-- Training Entity information --}}
            <x-certification.training-entity :certification="$certification" />

            {{-- Instructor information if available --}}
            @if($showInstructorInfo)
                <x-certification.instructor-info
                    :mainInstructor="$mainInstructor"
                    :assistants="$assistants"
                />
            @endif


        </x-slot>

        <x-slot name="sidebar">
            {{-- Card Preview --}}
            <div class="space-y-4">
                <x-certification.preview :certification="$certification" />
            </div>

            {{-- Activity Log --}}
            <div class="mt-6">
                @livewire('widget-activity-log', [
                    'subject' => $certification,
                    'loadType' => 'poll'
                ])
            </div>
        </x-slot>
    </x-certification.layout>
</x-layout>
