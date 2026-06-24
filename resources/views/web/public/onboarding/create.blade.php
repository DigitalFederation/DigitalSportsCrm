<x-guest-layout>
    <div
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 bg-cover bg-waves-full-bg-one">


        <x-authentication-card-logo />


        <div class="my-8 text-center">
            <h1 class="text-2xl md:text-3xl text-slate-600 font-bold"> {{ __('Activate Your :app Account', ['app' => config('branding.primary.portal_name', config('app.name', 'Digital Sports CRM'))]) }} </h1>
            <p class="text-slate-600">{{ __('Welcome! Let’s get your account set up and ready to use.') }}</p>
        </div>

        <div class="w-full md:w-1/3 md:mx-auto">
            <x-information-box
                title="Welcome!"
                :body="__('To complete the setup of your new account, please enter your email address below. We will
                send you an email with a link to create your password and activate your account. This link will also
                allow you to access your account for the first time')">
            </x-information-box>
        </div>

        <section class="card w-full md:w-1/3 md:mx-auto">

            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="block">
                    <x-label for="email" value="{{ __('Your email address') }}" />
                    <x-input id="email"
                             class="block mt-1 w-full"
                             type="email"
                             name="email"
                             :value="old('email')"
                             required
                             autofocus />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button type="submit" class="btn btn-primary px-4">{{ __('Send activation link') }}</button>
                </div>
            </form>

        </section>
    </div>
</x-guest-layout>
