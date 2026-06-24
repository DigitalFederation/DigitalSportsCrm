<div>
    <section>
        <label class="block text-sm font-medium mb-1" for="sport">{{ __('Discipline') }}</label>
        <select name="sport" id="sport" class="w-full form-select" wire:model.live="disciplineSelected">
            <option value="0" selected disabled hidden></option>
            @foreach($event->disciplines as $discipline)
                <option value="{{ $discipline->id }}">{{ $discipline->name }}</option>
            @endforeach
        </select>

        <div class="mt-4">
            <label class="block text-sm font-medium mb-1" for="filter_member_code">{{ __('main.Member Code') }}</label>
            <input wire:model.live="athleteCode" type="text" class="w-full form-input" name="athlete" id="filter_member_code">
            <button wire:click="findAthlete" type="button" class="mt-2 ml-0 btn btn-action w-full">{{ __('Find athlete') }}</button>
            <p><small class="text-red-500">{{ $errorMessage }}</small></p>
        </div>
    </section>

    @if(!empty($athlete))
        <div>
            <div class="flex flex-col">

                <div class="bg-blue-100 border border-blue-100 shadow-md rounded-xl p-4 mt-4">
                    <div class="flex-none sm:flex">
                        <div class="relative md:h-24 md:w-24 sm:mb-0 mb-3">

                            <div class="inline-flex ml-1 -mt-8 md:mt-0 mb-4 sm:mb-0 h-12 w-12 md:h-24 md:w-24 items-center">
                                <a href="{{ route('entity.individual.show', $athlete->id)}}" target="_blank" class="hover:underline flex">
                                    <x-secure-profile-image :individual="$athlete" size="thumb" class="object-fit rounded-full border-4 border-white w-full h-full" />
                                </a>
                            </div>

                        </div>
                        <div class="flex-auto sm:ml-5 justify-evenly">
                            <div class="flex items-center justify-between sm:mt-2">
                                <div class="flex items-center">
                                    <div class="flex flex-col">
                                        <div class="w-full flex-none text-lg text-gray-600 font-bold leading-none">{{ $athlete->name }} {{ $athlete->surname }}</div>
                                        <div class="flex-auto text-gray-400 my-1">
                                            <span class="mr-3 text-gray-500">Nationality</span><span>{{ $athlete->country->name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-row items-center">
                                <a href="{{ route('federation.individual.show', $athlete->id)}}" target="_blank" class="text-sm ml-0 flex-no-shrink btn-action bg-gray-400 hover:bg-gray-500 ">Details</a>
                                <button class="text-sm flex-no-shrink bg-green-400 hover:bg-green-500 btn-action transition ease-in duration-300" wire:click="inviteAthlete"> Send Invitation</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endif
</div>
