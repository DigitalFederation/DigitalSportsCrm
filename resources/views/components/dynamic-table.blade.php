<div class="bg-white shadow-sm rounded-md  border-slate-200 w-full">
    <div class="overflow-x-auto rounded-md">
        <table class="dynamic-table table-auto w-full">
            @if($displayableHeaders)
                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                <tr>
                    @foreach($headers as $header)
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap {{ $header['alignment'] }} last:text-right">


                            @if($header['sortable'])
                                @php
                                    $isCurrentSort = $header['field'] === $currentSortField;
                                    $sortDirection = $isCurrentSort && $currentSortDirection === 'asc' ? '-' : '';
                                    $sortLink = request()->fullUrlWithQuery(['sort' => $sortDirection . $header['field']]);
                                    $iconDirection = $isCurrentSort ? ($currentSortDirection === 'asc' ? '▲' : '▼') : '↕';
                                @endphp
                                <a href="{{ $sortLink }}" class="hover:text-blue-400 flex items-center gap-1">
                                    <span>{{ $header['text'] }}</span>
                                    <span class="text-gray-400">{{ $iconDirection }}</span>
                                </a>
                            @else
                                {{ $header['text'] }}
                            @endif
                        </th>
                    @endforeach
                </tr>
                </thead>
            @endif
            <tbody class="text-sm divide-y divide-slate-200">
            {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
