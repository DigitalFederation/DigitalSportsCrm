@props(['color', 'status'])

<span class="inline-block rounded border px-2 py-0.5 leading-tight text-sm font-semibold w-fit border-{{ $color }} text-{{ $color }}">
    <div class="hidden border-green-600 text-green-600 border-red-600 text-red-600 border-blue-600 text-blue-600 border-yellow-400 text-yellow-400 border-slate-400 text-slate-400 border-slate-500 text-slate-500"></div>
    {{ $status }}
</span>
