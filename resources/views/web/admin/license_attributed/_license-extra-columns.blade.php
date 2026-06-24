@php
    $owner = $license->owner;
    $isIndividual = ($holderType === 'individual') || ($license->model_type ?? null) === 'individual';
@endphp

<td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
    @if($isIndividual && $owner)
        {{ $owner->member_number ?? '-' }}
    @elseif(!$isIndividual && $owner)
        {{ $owner->nif ?? '-' }}
    @else
        -
    @endif
</td>

<td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
    @if($isIndividual && $owner)
        {{ $owner->doc_ref ?? '-' }}
    @elseif(!$isIndividual && $owner)
        {{ $owner->entity_type ?? '-' }}
    @else
        -
    @endif
</td>
