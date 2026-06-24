<div>
    {{-- Back Button Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <a href="{{
                    $this->model instanceof \Domain\Federations\Models\Federation
                        ? route('federation.evt-events.events.show', $event)
                        : route('entity.evt-events.events.show', $event)
                }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <x-heroicon-m-arrow-left class="w-5 h-5 mr-2 -ml-1" />
                    {{ __('events.back_to_event', ['event' => $event->name]) }}
                </a>
            </div>
            <div class="flex items-center text-sm text-gray-500">
                <x-heroicon-m-calendar class="w-5 h-5 mr-1" />
                {{ $event->start_date->format('M d, Y') }} - {{ $event->end_date->format('M d, Y') }}
            </div>
        </div>
    </div>
    <div class="space-y-6">
        {{-- Header with Context --}}
        <div class="border-b pb-4">
            <h2 class="text-2xl font-semibold text-gray-900">{{ $event->name }} - {{ __('events.enrolled_athletes') }}</h2>
            <p class="mt-1 text-sm text-gray-600">{{ __('events.list_of_enrolled_athletes') }}</p>
        </div>

        {{-- Table Component --}}
        <div class="bg-white rounded-lg shadow">
            {{ $this->table }}
        </div>
    </div>
</div>
