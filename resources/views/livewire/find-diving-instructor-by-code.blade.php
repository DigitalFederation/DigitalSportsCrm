<div>

    <section>
        <label class="block text-sm font-medium mb-1" for="professional_role">{{ __('Choose the Professional') }}</label>
        <select name="professional_role" id="professional_role" class="w-full form-select"
            wire:model.live="professionalRoleSelected">
            <option value="0" selected disabled hidden></option>
            @foreach ($professionalRoles as $professionalRole)
                <option value="{{ $professionalRole->id }}">{{ $professionalRole->name }}</option>
            @endforeach
        </select>

        <div class="mt-4">
            <label class="block text-sm font-medium mb-1" for="find_instructor_member_code">{{ __('main.Member Code') }}</label>
            <div class="flex items-end">
                <input wire:model.live="instructorCode" type="text" class="w-full form-input rounded-r-none"
                    name="individual" id="find_instructor_member_code" {{ $entityId === null ? 'disabled' : '' }}>
                <x-entity-instructor-selector :entity-id="$entityId" inputId="find_instructor_member_code"
                    wireModel="instructorCode" />
            </div>
            <button wire:click="findInstructor" type="button" class="mt-2 ml-0 btn btn-action w-full"
                {{ $entityId === null ? 'disabled' : '' }}>{{ __('Find Instructor') }}</button>
            <p><small class="text-red-500">{{ $errorMessage }}</small></p>
        </div>

    </section>

    @if (!empty($instructor))
        <section class="bg-gray-50 rounded-lg p-6 animate-fade-in">
            <div class="flex items-center space-x-6">
                <div class="flex-shrink-0">
                    <a href="{{ route('entity.individual.show', $instructor->id) }}" target="_blank"
                        class="block relative">
                        <x-secure-profile-image :individual="$instructor" size="thumb" class="h-24 w-24 rounded-full object-cover border-4 border-white shadow-lg" />
                    </a>
                </div>
                <div class="flex-grow">
                    <h3 class="text-xl font-semibold text-gray-800">{{ $instructor->name }} {{ $instructor->surname }}
                    </h3>
                    <p class="text-gray-600">
                        <span class="font-medium">{{ __('Nationality') }}:</span> {{ $instructor->country->name }}
                    </p>
                    <div class="mt-4 flex space-x-3">
                        <a href="{{ route('entity.individual.show', $instructor->id) }}" target="_blank"
                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Details') }}
                        </a>
                        <button wire:click="inviteInstructor"
                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            {{ __('Invite') }}
                        </button>
                    </div>
                </div>
            </div>
        </section>
    @endif

</div>
