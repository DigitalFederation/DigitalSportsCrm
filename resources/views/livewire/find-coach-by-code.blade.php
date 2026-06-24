<div>

    <section>
        <label class="block text-sm font-medium mb-1" for="license">{{ __('Sport') }}</label>
        <select name="license" id="license" class="w-full form-select"
                wire:model.live="licenseSelected">
            <option value="0" selected disabled hidden></option>
            @foreach($licenses as $license)
                <option value="{{ $license->id }}">{{ $license->sport->translated_name }}</option>
            @endforeach
        </select>

        <div class="mt-4">
            <label class="block text-sm font-medium mb-1" for="filter_member_code">{{ __('main.Member Code') }}</label>
            <input wire:model.live="coachCode" type="text" class="w-full form-input" name="athlete"
                   id="filter_member_code">
            <button wire:click="findCoach" type="button"
                    class="mt-2 ml-0 btn btn-action w-full">{{ __('Find coach') }}</button>
            <p><small class="text-red-500">{{ $errorMessage }}</small></p>
        </div>

    </section>

    @if(!empty($coach))
        <div>
            <div class="flex flex-col">

                <div class="bg-blue-100 border border-blue-100 shadow-md rounded-xl p-4 mt-4">
                    <div class="flex-none sm:flex">
                        <div class="relative md:h-24 md:w-24 sm:mb-0 mb-3">

                            <div
                                class="inline-flex ml-1 -mt-8 md:mt-0 mb-4 sm:mb-0 h-12 w-12 md:h-24 md:w-24 items-center">
                                <a href="{{ route('entity.individual.show', $coach->id)}}" target="_blank"
                                   class="hover:underline flex">
                                    <x-secure-profile-image :individual="$coach" size="thumb" class="object-fit rounded-full border-4 border-white w-full h-full" />
                                </a>
                            </div>

                        </div>
                        <div class="flex-auto sm:ml-5 justify-evenly">
                            <div class="flex items-center justify-between sm:mt-2">
                                <div class="flex items-center">
                                    <div class="flex flex-col">
                                        <div
                                            class="w-full flex-none text-lg text-gray-600 font-bold leading-none">{{ $coach->name }} {{ $coach->surname }}</div>
                                        <div class="flex-auto text-gray-400 my-1">
                                            <span
                                                class="mr-3 text-gray-500">Nationality</span><span>{{ $coach->country->name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-row items-center gap-x-2">
                                <a href="{{ route('entity.individual.show', $coach->id)}}"
                                   target="_blank"
                                   class="text-sm ml-0 flex-no-shrink btn btn-xs btn-info"> {{ __('Details') }}</a>
                                <button
                                    class="text-sm flex-no-shrink btn btn-xs btn-info bg-green-600"
                                    wire:click="inviteCoach"> {{ __('Invite') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endif

</div>
