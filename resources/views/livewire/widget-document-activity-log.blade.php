<div>
    @if(!empty($activitiesByDate))
        <div class="card mb-4"> <!-- Adjust the size as needed -->
            <h2 class="text-lg font-semibold mb-3"> {{ __('Audit Log') }}</h2>
            <ol class="relative border-l border-gray-200 dark:border-gray-700 h-80 overflow-y-scroll overflow-x-hidden">
                @foreach($activitiesByDate as $date => $activities)
                    @foreach($activities as $activity)
                        <li class="mb-6 ml-4">
                            <div
                                class="absolute w-3 h-3 bg-gray-200 rounded-full mt-1.5 -left-1.5 border border-white"></div>
                            <time class="mb-1 text-sm font-normal leading-none text-gray-400 ">{{ $date }}</time>
                            <p class="text-sm font-semibold text-gray-500">{{ $activity->description }}</p>
                        </li>
                    @endforeach
                @endforeach
            </ol>
        </div>
    @endif

</div>
