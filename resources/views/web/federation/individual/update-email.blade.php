@section('title', __('Update Individual Email'))
<x-layout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page header -->
        <div class="mb-8">
            <h1 class="page-first-title">{{ __('Update Email for') }} {{ $individual->name }}</h1>
        </div>

        <!-- Information box -->
        <div class="mb-8">
            <x-information-box
                title="{{ __('Email Update Information') }}"
                :body="__('This form allows you to update email addresses for this individual. The public email is used for communications and certificates. The login email is used for account access. You can update either or both.')"
            />
        </div>

        <!-- Main Form Card -->
        <div class="card">

            <form action="{{ route(Request::segment(1).'.individual.update-email', $individual) }}"
                    method="POST"
                    class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Public Email Section -->
                <div class="space-y-4">
                    <div class="border-b border-gray-200 pb-4">
                        <h2 class="text-lg font-medium text-gray-900 mb-1">
                            {{ __('Public Email') }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ __('This email will be used for communications and displayed on certificates.') }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700" for="public_email">
                                {{ __('New Public Email') }} <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-sm text-gray-500">
                                {{ __('Current: ') }} {{ $individual->email }}
                            </span>
                        </div>
                        <input type="email"
                            id="public_email"
                            name="public_email"
                            class="form-input w-full {{ $errors->has('public_email') ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500' }}"
                            value="{{ old('public_email') }}"
                            aria-describedby="public_email_error"
                            required>
                        @if ($errors->has('public_email'))
                            <p class="text-sm text-rose-600" id="public_email_error">
                                {{ $errors->first('public_email') }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Login Email Section -->
                <div class="space-y-4 pt-4">
                    <div class="border-b border-gray-200 pb-4">
                        <h2 class="text-lg font-medium text-gray-900 mb-1">
                            {{ __('Login Email') }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ __('This email is used to log into the system.') }}
                        </p>
                    </div>

                    <div>
                        <label class="inline-flex items-center text-sm">
                            <input type="checkbox"
                                name="update_login_email"
                                value="1"
                                class="form-checkbox rounded text-blue-600 focus:ring-blue-500"
                                {{ old('update_login_email') ? 'checked' : '' }}
                                aria-describedby="login_email_section">
                            <span class="ml-2 font-medium">{{ __('Also update login email') }}</span>
                        </label>
                    </div>

                    <div class="space-y-2" id="login_email_section">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700" for="login_email">
                                {{ __('New Login Email') }}
                            </label>
                            <span class="text-sm text-gray-500">
                                {{ __('Current: ') }} {{ $individual->user->email }}
                            </span>
                        </div>
                        <input type="email"
                            id="login_email"
                            name="login_email"
                            class="form-input w-full {{ $errors->has('login_email') ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500' }}"
                            value="{{ old('login_email') }}"
                            aria-describedby="login_email_error">
                        @if ($errors->has('login_email'))
                            <p class="text-sm text-rose-600" id="login_email_error">
                                {{ $errors->first('login_email') }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="pt-6 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row-reverse sm:space-x-4 sm:space-x-reverse space-y-3 sm:space-y-0">
                        <button type="submit" class="btn btn-primary w-full sm:w-auto">
                            {{ __('Update Email') }}
                        </button>
                        <a href="{{ route(Request::segment(1).'.individual.show', $individual) }}"
                            class="btn btn-secondary w-full sm:w-auto text-center">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-layout>
