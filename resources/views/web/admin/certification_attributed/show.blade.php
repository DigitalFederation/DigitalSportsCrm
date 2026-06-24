<x-layout>
    <x-certification.layout :certification="$certification">
        <x-slot name="mainContent">
            <x-certification.details :certification="$certification" />
            <x-certification.organization-info :certification="$certification" />
            <x-certification.training-entity :certification="$certification" />

            @if($showInstructorInfo)
                <x-certification.instructor-info
                    :mainInstructor="$mainInstructor"
                    :assistants="$assistants"
                />
            @endif

            @if(auth()->user()->group()->first()->code == 'ADMIN')
                <div class="bg-white rounded-lg shadow divide-y divide-gray-200">
                    <div class="px-6 py-4">
                        <h2 class="text-lg font-bold text-gray-900">{{ __('certifications.notes') }}</h2>
                    </div>
                    <div class="px-6 py-4">
                        @if($certification->notes)
                            <p class="text-sm text-gray-900">{{ $certification->notes }}</p>
                        @else
                            <p class="text-sm text-gray-500">{{ __('certifications.details.no_notes') }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </x-slot>

        <x-slot name="sidebar">
            <div class="space-y-4">
                <x-certification.preview :certification="$certification" />
            </div>

            <div class="mt-6">
                @livewire('widget-activity-log', [
                    'subject' => $certification,
                    'loadType' => 'poll'
                ])
            </div>
        </x-slot>
    </x-certification.layout>
</x-layout>
