<div>
    @if (session()->has('success'))
        <x-layout.banner_message />
    @endif

    <div class="flex flex-col md:flex-row gap-x-4">
        <div class="w-full md:w-2/3">
            <form wire:submit.prevent="addPin">

                <div class="w-full mt-4">
                    <label class="block text-sm font-medium mb-1" for="pin"> PIN <span
                            class="text-rose-500">*</span></label>
                    <input type="number" name="pin" id="pin" wire:model="pin" class="form-input w-full" required>
                    <p class="text-xs text-slate-400">Use a pin to be used as a key to give access to the public
                        page</p>
                    @error('pin') <span class="text-xs mt-1 text-rose-500 h-2">{{ $message }}</span> @enderror
                </div>

                <div class="mt-4 w-full border-gray-300 bg-slate-100 px-4 py-2 rounded-md text-sm font-mono">
                    Link: {{ url('/ad/events') }}
                </div>


                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Add PIN</button>
                </div>

            </form>
        </div>
        <div class="w-full md:w-1/3">
            @if($pins)
                <!-- List existing PINs -->
                <div class="mt-2">
                    <h2 class="text-sm font-semibold">Existing PINs:</h2>
                    <ul>
                        @foreach($pins as $pin)
                            <li class="flex justify-between items-center mt-2 p-2 bg-gray-100 rounded">
                                {{ $pin->pin }}
                                <button wire:click="removePin({{ $pin->id }})" class="text-red-500 hover:text-red-700">
                                    Remove
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
