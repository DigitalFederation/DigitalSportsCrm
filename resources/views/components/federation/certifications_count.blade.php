<div class="mt-6 card flex flex-col">

    <header class="pb-2 border-b border-slate-100 flex justify-between">
        <h2 class="font-semibold text-slate-800">{{ __('Certification Slot Totals')}}</h2>
        <div class="text-secondary font-medium">
            <a href="{{ route('admin.certification-slot.index', ['filter[filter_federation]' => $federation->id]) }}" class="text-xs py-0 btn-xs btn-outline hover:btn-outline-hover">
                <span>{{ __('View All') }}</span>
            </a>
        </div>
    </header>

    <div class="overflow-x-auto mt-4 w-full">

        @if(!empty($slots))
            <table class="w-full bg-transparent" role="table">
                <thead role="rowgroup">
                <tr role="row">
                    <th role="columnheader" class="text-left" aria-sort="none"> Certification</th>
                    <th role="columnheader" class="text-right" aria-sort="none"> Un. Ordered</th>
                    <th role="columnheader" class="text-right" aria-sort="none"> Un. Available</th>
                </tr>
                </thead>
                <tbody role="rowgroup">
                @foreach($slots as $key => $slot)
                    <tr role="row">
                        <td role="cell">
                    <span class="pr-6 font-medium text-sm text-secondary whitespace-nowrap">
                        {{ $key }}
                    </span>
                        </td>
                        <td role="cell" class="text-right">
                    <span class="font-medium text-sm text-secondary whitespace-nowrap">
                        {{ $slot['quantity_original'] }}
                    </span>
                        </td>
                        <td role="cell" class="text-right">
                    <span class="font-medium text-sm text-secondary whitespace-nowrap">
                        {{ $slot['quantity_real'] }}
                    </span>
                        </td>

                    </tr>
                @endforeach
                </tbody>

            </table>
        @else

            <div class="flex flex-col items-center justify-center h-64">
                <div class="text-secondary font-medium">No certification slot requests</div>
            </div>

        @endif

    </div>
</div>
