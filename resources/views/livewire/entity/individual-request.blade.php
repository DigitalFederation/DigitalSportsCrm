<div class="mt-4">

    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
        <p class="text-sm text-blue-800">{{ __('entity.member_request_description') }}</p>
    </div>

    <form wire:submit.prevent="submit" class="mt-4">

        <div class="flex flex-col w-full sm:w-1/2">
            <label for="member_code" class="block text-sm font-medium mb-1">
                {{ __('main.personal_id') }}
            </label>
            <input wire:model="member_code" type="text" name="member_code" id="member_code"
                   class="form-input w-full @error('member_code') border-rose-300 @enderror"
                   placeholder="{{ __('entity.valid_member_code') }}" />
            @error('member_code')
                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex items-center my-3 w-full sm:w-1/2">
            <div class="flex-grow border-t border-gray-300"></div>
            <span class="mx-3 text-xs font-medium text-gray-500 uppercase">{{ __('entity.or_separator') }}</span>
            <div class="flex-grow border-t border-gray-300"></div>
        </div>

        <div class="flex flex-col w-full sm:w-1/2">
            <label for="member_number" class="block text-sm font-medium mb-1">
                {{ __('entity.member_number') }}
            </label>
            <input wire:model="member_number" type="text" name="member_number" id="member_number"
                   class="form-input w-full @error('member_number') border-rose-300 @enderror"
                   placeholder="{{ __('entity.member_number') }}" />
            @error('member_number')
                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex flex-wrap justify-end space-x-2 mt-6 border-t pt-4">
            <button x-on:click="showModal = false" type="button" class="btn btn-secondary">
                {{ __('common.cancel') }}
            </button>
            <button type="submit" class="btn btn-primary">
                {{ __('entity.submit_request') }}
            </button>
        </div>

    </form>
</div>
