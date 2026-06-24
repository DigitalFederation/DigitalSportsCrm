@props(['status'])

@if($status === 'paid')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
        {{ __('licenses.payment_status_paid') }}
    </span>
@elseif($status === 'pending_payment')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
        {{ __('licenses.payment_status_pending_payment') }}
    </span>
@else
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
        {{ __('licenses.payment_status_no_document') }}
    </span>
@endif
