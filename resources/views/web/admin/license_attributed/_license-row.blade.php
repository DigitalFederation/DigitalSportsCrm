@props(['license', 'holderType', 'showSport' => false, 'showPaymentStatus' => true])

@php
    $owner = $license->owner;
    $isIndividual = ($holderType === 'individual') || ($license->model_type ?? null) === 'individual';
@endphp

<tr>
    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
        {{ $license->license_name }}
    </td>

    @if($showSport)
        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
            {{ $license->license?->sport?->translated_name ?? '-' }}
        </td>
    @endif

    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
        @if($isIndividual && $owner)
            <a href="{{ route('admin.individual.show', $owner->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                {{ $owner->name }} {{ $owner->surname }}
            </a>
        @elseif(!$isIndividual && $owner)
            <a href="{{ route('admin.entity.show', $owner->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                {{ $owner->name }}
            </a>
        @else
            {{ $license->holder_name }}
        @endif
    </td>

    {!! $extraColumns ?? '' !!}

    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
        @if($license->date_begin)
            {{ $license->date_begin->format('d/m/Y') }}
        @elseif($license->current_term_starts_at)
            {{ $license->current_term_starts_at->format('d/m/Y') }}
        @else
            -
        @endif
    </td>

    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
        @if($license->date_expire)
            {{ $license->date_expire->format('d/m/Y') }}
        @elseif($license->current_term_ends_at)
            {{ $license->current_term_ends_at->format('d/m/Y') }}
        @else
            -
        @endif
    </td>

    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
        <x-tables.badge :status="ucfirst($license->stateName())" :color="$license->stateColor()" />
    </td>

    @if($showPaymentStatus)
        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
            <x-tables.payment-status-badge :status="$license->payment_status" />
        </td>
    @endif

    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
        <div class="space-x-1 flex justify-end">
            <x-dynamic-table-buttons type="show"
                                     :route="route('admin.license-attributed.show', $license->id)" />
            <x-dynamic-table-buttons type="delete"
                                     :route="route('admin.license-attributed.delete', $license->id)"
                                     method="DELETE" />
        </div>
    </td>
</tr>
