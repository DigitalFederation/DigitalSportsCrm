<x-layout>
    <div class="previous-layout-classes">

        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div>
                <h1 class="page-first-title">
                    {{ $competition->full_name }}
                </h1>
                <p class="text-lg">{{ __('Disciplines') }}</p>
            </div>
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{route('federation.evt-events.events.show', $competition->event_id)}}"
                   class="btn btn-outline btn-xs"> {{ __('Back') }} </a>
            </div>
        </div>


        <div>
            @if(!empty($disciplines) && $disciplines->count() > 0)
                <div class="card w-full">
                    @foreach ($disciplines as $discipline)
                        <div class="flex justify-between my-2 items-center">
                            <div>
                                <span class="font-bold">{{ $discipline->name }}</span> |
                                <span>{{ $discipline->enrollment_type }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-utility.no-data></x-utility.no-data>
            @endif
        </div>


    </div>

</x-layout>
