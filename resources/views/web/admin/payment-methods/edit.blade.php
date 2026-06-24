<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('payment_admin.edit_method') }}: {{ $paymentMethod->name }}</h1>
            </div>
            <div>
                <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary">
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('admin.payment-methods.update', $paymentMethod->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('payment_admin.name') }}
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $paymentMethod->name) }}"
                               class="form-input w-full rounded-md border-gray-300"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Instructions -->
                    <div>
                        <label for="instructions" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('payment_admin.instructions') }}
                        </label>
                        <textarea name="instructions"
                                  id="instructions"
                                  rows="4"
                                  class="form-textarea w-full rounded-md border-gray-300">{{ old('instructions', $paymentMethod->instructions) }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">{{ __('payment_admin.instructions_help') }}</p>
                        @error('instructions')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Enabled -->
                    <div class="flex items-center">
                        <input type="hidden" name="is_enabled" value="0">
                        <input type="checkbox"
                               name="is_enabled"
                               id="is_enabled"
                               value="1"
                               {{ old('is_enabled', $paymentMethod->is_enabled) ? 'checked' : '' }}
                               class="form-checkbox h-5 w-5 text-blue-600 rounded">
                        <label for="is_enabled" class="ml-2 text-sm text-gray-700">
                            {{ __('payment_admin.enabled') }}
                        </label>
                    </div>

                    <!-- Read-only fields -->
                    <div class="border-t pt-6 mt-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-4">{{ __('payment_admin.technical_info') }}</h3>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">{{ __('payment_admin.driver') }}:</span>
                                <code class="ml-2 bg-gray-100 px-2 py-1 rounded">{{ $paymentMethod->driver }}</code>
                            </div>
                            <div>
                                <span class="text-gray-500">{{ __('payment_admin.handler') }}:</span>
                                <code class="ml-2 bg-gray-100 px-2 py-1 rounded text-xs">{{ class_basename($paymentMethod->handler) }}</code>
                            </div>
                        </div>

                        @if($paymentMethod->driver === 'easypay')
                            <div class="mt-4 p-4 bg-yellow-50 rounded-md">
                                <p class="text-sm text-yellow-800">
                                    <strong>{{ __('payment_admin.note') }}:</strong>
                                    {{ __('payment_admin.easypay_config_note') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layout>
