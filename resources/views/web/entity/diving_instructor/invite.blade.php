@section('title', __('diving.invite_instructor'))
<x-layout>
    <div class="previous-layout-classes">
        
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('diving.invite_instructor') }}</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('entity.diving-instructor.index') }}" class="btn btn-secondary">
                    {{ __('common.back') }}
                </a>
            </div>
        </div>

        <!-- Information Box -->
        <div class="information-box mb-6">
            <p class="text-sm text-slate-600">
                {{ __('diving.instructor_invitation_info') }}
            </p>
        </div>

        <!-- Form -->
        <form x-data="{ submitting: false, message: @js(old('message', '')), max: 500 }"
              @submit.prevent="submitting = true; $el.submit()"
              action="{{ route('entity.diving-instructor.send_invitation') }}" method="POST" class="card">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Professional Selection -->
                <div class="sm:col-span-1">
                    <label for="individual_code" class="block text-sm font-medium mb-1">
                        {{ __('diving.select_instructor') }} <span class="text-rose-500">*</span>
                    </label>
                    <p id="individual_code_help" class="text-gray-500 text-sm mb-2">
                        {{ __('diving.enter_member_or_member_code') }}
                    </p>
                    <div class="flex gap-2">
                        <input id="individual_code"
                               name="individual_code"
                               type="text"
                               inputmode="numeric"
                               autocomplete="off"
                               autocapitalize="none"
                               spellcheck="false"
                               @error('individual_code') aria-invalid="true" aria-describedby="individual_code_error" @else aria-describedby="individual_code_help" @enderror
                               class="form-input w-full @error('individual_code') border-rose-300 @enderror"
                               value="{{ old('individual_code') }}"
                               placeholder="{{ __('diving.member_number_placeholder') }}"
                               required>
                    </div>
                    @error('individual_code')
                        <div id="individual_code_error" class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Professional Role Selection -->
                <div class="sm:col-span-1">
                    <label for="professional_role_id" class="block text-sm font-medium mb-1">
                        {{ __('diving.select_role') }} <span class="text-rose-500">*</span>
                    </label>
                    <p id="professional_role_help" class="text-gray-500 text-sm mb-2">
                        {{ __('diving.select_role_description') }}
                    </p>
                    <select id="professional_role_id"
                            name="professional_role_id"
                            @error('professional_role_id') aria-invalid="true" aria-describedby="professional_role_error" @else aria-describedby="professional_role_help" @enderror
                            class="form-select w-full @error('professional_role_id') border-rose-300 @enderror"
                            required>
                        <option value="">{{ __('diving.select_role_placeholder') }}</option>
                        @foreach($professionalRoles as $role)
                            <option value="{{ $role->id }}" @selected(old('professional_role_id') == $role->id)>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('professional_role_id')
                        <div id="professional_role_error" class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Message -->
                <div class="sm:col-span-2">
                    <label for="message" class="block text-sm font-medium mb-1">
                        {{ __('diving.personal_message') }}
                    </label>
                    <p id="message_help" class="text-gray-500 text-sm mb-2">
                        {{ __('diving.optional_invitation_message') }}
                    </p>
                    <textarea id="message"
                              name="message"
                              rows="4"
                              maxlength="500"
                              x-model="message"
                              @error('message') aria-invalid="true" aria-describedby="message_error" @else aria-describedby="message_help" @enderror
                              class="form-textarea w-full @error('message') border-rose-300 @enderror"
                              placeholder="{{ __('diving.invitation_message_placeholder') }}">{{ old('message') }}</textarea>
                    <div class="text-xs text-slate-500 mt-1" aria-live="polite" x-text="`${message?.length || 0} / ${max}`"></div>
                    @error('message')
                        <div id="message_error" class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex flex-wrap justify-end space-x-2 mt-6">
                <a href="{{ route('entity.diving-instructor.index') }}" class="btn btn-secondary">
                    {{ __('common.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary" :disabled="submitting" :aria-busy="submitting.toString()">
                    {{ __('diving.send_invitation') }}
                </button>
            </div>
        </form>
    </div>
</x-layout>