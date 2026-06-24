<x-layout>
  <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

      <div class="mb-8 flex justify-between items-center">
          <h1 class="text-3xl font-bold text-gray-900">{{ __('Member Subscription') }}</h1>
      </div>

      <x-information-box
              title="{{ __('Information') }}"
              body="{{ __('This screen displays all subscriptions for your entity members. You can filter and view subscription details from here.') }}">
      </x-information-box>


  </div>
</x-layout>