@props(['document', 'route' => null])

@php
    $formRoute = $route ?? route('admin.document.manual-payment', $document);
@endphp

<x-information-box
    title="{{ __('Information') }}"
    body="{{ __('Use the following form to manually mark this document as paid.') }}">
</x-information-box>


<form
    class="mt-4"
    action="{{ $formRoute }}"
    method="POST"
    onsubmit="return confirm('{{ __('Are you sure you want to save this payment?') }}') ">
    @csrf

    <div class="mb-4">
        <label class="block text-sm mb-1" for="comment"> {{ __('Notes') }}</label>
        <textarea name="comment" id="comment" cols="30" rows="2"
                  class="w-full border border-zinc-200 rounded-md p-2"></textarea>
    </div>

    <!-- Payment Amount Field -->
    <div class="mb-4">
        <label for="amount" class="block text-sm mb-1">{{ __('Payment amount') }}</label>
        <input type="number" name="amount" id="amount" step="0.01" min="0.01"
               class="w-full border border-zinc-200 rounded-md p-2"
               placeholder="{{ __('Enter payment amount') }}" required>
        <p class="text-xs mt-1 text-slate-500">{{ __('Enter the amount that is being paid for this document.') }}</p>
    </div>

    <!-- Add note for remaining amount -->
    @if($document->amount_paid < $document->total_value)
        <div class="mb-4">
            <p class="text-sm text-slate-500">
                {{ __('Remaining Amount to be Paid:') }} {{ money($document->total_value - $document->amount_paid, $document->currency) }}
            </p>
        </div>
    @endif

    <!-- Moloni Invoice Checkbox -->
    <div class="mb-4">
        <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" name="create_moloni_invoice" value="1"
                   class="w-4 h-4 text-action bg-white border-zinc-300 rounded focus:ring-action focus:ring-2">
            <span class="ml-2 text-sm text-slate-700">{{ __('documents.create_moloni_invoice') }}</span>
        </label>
        <p class="text-xs mt-1 text-slate-500">{{ __('documents.create_moloni_invoice_description') }}</p>
    </div>

    <button type="submit"
            class="text-md w-full justify-center inline-flex font-medium rounded-md text-center mt-4 px-6 py-2 text-white bg-action">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
             stroke="currentColor" class="w-6 h-6 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
        </svg>
        <span>{{ __('Register payment') }}</span>
    </button>
</form>
