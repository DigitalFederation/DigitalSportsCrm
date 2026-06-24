<div>
    <div class="card mb-4 max-h-80 overflow-y-auto  ">
        <h2 class="text-lg font-semibold mb-2"> {{ __('main.audit_log') }}</h2>

            @if($loadType === 'lazy' && empty($activitiesByDate))
                <button wire:click="loadActivities" class="btn btn-info mb-4">
                    {{ __('main.load_activity_log') }}
                </button>
            @endif

            @if(!empty($activitiesByDate))

                <ol class="relative border-l border-gray-200 dark:border-gray-700">
                    @foreach($activitiesByDate as $date => $activities)
                        @foreach($activities as $activity)
                            <li class="mb-4 ml-4">
                                <div class="absolute w-3 h-3 bg-gray-200 rounded-full mt-1.5 -left-1.5 border border-white"></div>
                                <time class="mb-1 text-sm font-normal leading-none text-gray-400 ">{{ date('d/m/Y', strtotime($date)) }}</time>
                                <p class="text-sm font-semibold text-gray-500">{{ __('activity.' . Str::snake($activity->description), [], app()->getLocale()) !== 'activity.' . Str::snake($activity->description) ? __('activity.' . Str::snake($activity->description)) : $activity->description }}</p>
                            </li>
                        @endforeach
                    @endforeach
                </ol>
            @else
                <div class="text-gray-500 text-sm font-semibold mt-3 italic">
                    {{ __('main.no_activity_logs') }}
                </div>
            @endif

    </div>
</div>
