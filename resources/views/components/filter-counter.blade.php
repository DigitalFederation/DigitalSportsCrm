@if(!empty($filtersToCount))
    <div class="mb-3 flex gap-x-5">
        @foreach($filtersToCount as $key => $filter)
            <div class="card flex-auto col-span-2 sm:col-span-1 flex flex-col items-center justify-center py-8 px-1 rounded-2xl text-secondary">
                <div class="text-5xl font-semibold leading-none tracking-tight">{{ $filter }}</div>
                <div class="mt-1 text-sm font-medium text-center">{{ $key }}</div>
            </div>
        @endforeach
    </div>
@endif
