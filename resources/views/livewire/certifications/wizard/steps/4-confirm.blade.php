<div class="space-y-4">
    <h2 class="text-lg font-semibold">{{ __('Step 4: Confirm Attribution') }}</h2>
    <p class="text-sm text-gray-600">{{ __('Please review all the details below before submitting.') }}</p>

    {{-- Display Final Summary --}}
    <div class="space-y-3 p-4 border rounded bg-gray-50">
         <h3 class="text-md font-semibold border-b pb-2 mb-3">{{ __('Final Summary') }}</h3>

         {{-- Context --}}
         <div class="grid grid-cols-2 gap-4 text-sm">
             <div>
                 <span class="font-medium">{{ __('Federation') }}:</span>
                 <span>{{ $selectedFederation?->name ?? 'N/A' }}</span>
             </div>
              <div>
                 <span class="font-medium">{{ __('School') }}:</span>
                 <span>{{ $selectedSchool?->name ?? 'N/A' }}</span>
             </div>
         </div>

         {{-- Roles --}}
         @if($selectedDirector)
            <x-certifications.wizard.summary-card :title="__('Director')" :individual="$selectedDirector" />
        @endif
        @if($selectedAssistants->isNotEmpty())
            <x-certifications.wizard.summary-card :title="__('Assistants')" :individuals="$selectedAssistants" />
        @endif
         @if($selectedStudents->isNotEmpty())
            <x-certifications.wizard.summary-card :title="__('Students')" :individuals="$selectedStudents" />
        @endif

         {{-- Certification --}}
         @if($selectedCertification)
             <div class="pt-2 mt-2 border-t">
                 <span class="font-medium">{{ __('Certification') }}:</span>
                 <span>{{ $selectedCertification->name }}</span>
             </div>
         @endif

        {{-- Dates & Notes Summary --}}
        @if ($this->actorType === 'federation')
         <div class="grid grid-cols-2 gap-4 text-sm pt-2 mt-2 border-t">
             <div>
                 <span class="font-medium">{{ __('Issue Date') }}:</span>
                 <span>{{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('Y-m-d') : 'N/A' }}</span>
             </div>
              <div>
                 <span class="font-medium">{{ __('Expiration Date') }}:</span>
                 <span>{{ $expirationDate ? \Carbon\Carbon::parse($expirationDate)->format('Y-m-d') : 'N/A' }}</span>
             </div>
         </div>
         @endif
         @if($notes)
            <div class="text-sm pt-2 mt-2 border-t">
                 <p class="font-medium">{{ __('Notes') }}:</p>
                 <p class="whitespace-pre-wrap">{{ $notes }}</p>
            </div>
         @endif

         {{-- Approval --}}
         @if($federationApprove)
             <div class="pt-2 mt-2 border-t">
                 <p class="font-medium text-indigo-700">{{ __('Approved by National Technical Committee') }}</p>
             </div>
         @endif

    </div>

    {{-- Potential add notes section here --}}

</div>
