@php
    $owner = $license->owner;
@endphp

<td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
    {{ $owner?->member_number ?? '-' }}
</td>
