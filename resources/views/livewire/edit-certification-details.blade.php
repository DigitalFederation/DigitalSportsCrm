<div>
    @if (session()->has('success') || session()->has('error'))
        <x-layout.banner_message />
    @endif

    <form wire:submit.prevent="updateCertificationDetails" class="mt-4">

        <div class=" flex gap-x-4">
            <div class="w-1/3">
                <label class="block text-sm font-medium mb-1"> {{ __('Issue Date') }}</label>
                <input type="date" wire:model="current_term_starts_at" class="form-input w-full">
                @error('current_term_starts_at')
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="w-1/3">
                <label class="block text-sm font-medium mb-1"> {{ __('National Federation Number') }}</label>
                <input type="text" wire:model="national_code" class="form-input w-full">
                @error('national_code')
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="w-1/3">
                <label class="block text-sm font-medium mb-1"> {{ __('Expiration Date') }}</label>
                <input type="date" wire:model="current_term_ends_at" class="form-input w-full">
                @error('current_term_ends_at')
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <div class="mt-2 border-t border-slate-200 pt-2">
            <button class="btn-primary" type="submit">{{ __('Activate certification') }}</button>
        </div>
    </form>

</div>
