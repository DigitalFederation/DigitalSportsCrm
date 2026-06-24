<x-layout>
    <x-certification.layout :certification="$certification" cardRouteName="international-certification-card">
        <x-slot name="mainContent">
            <x-certification.validation-actions :certification="$certification" />

            <x-certification.details :certification="$certification" />

            <x-certification.organization-info :certification="$certification" />

            <x-certification.training-entity :certification="$certification" />

            @if ($showInstructorInfo)
                <x-certification.instructor-info :mainInstructor="$mainInstructor" :assistants="$assistants" />
            @endif
        </x-slot>

        <x-slot name="sidebar">
            <div class="space-y-4">
                <x-certification.preview :certification="$certification" />
            </div>

            <div class="mt-6">
                @livewire('widget-activity-log', [
                    'subject' => $certification,
                    'loadType' => 'poll',
                ])
            </div>
        </x-slot>
    </x-certification.layout>
</x-layout>
