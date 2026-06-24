@props([
    'currentStep' => 1,
    'steps' => [
        ['number' => 1, 'title' => __('events.step1_title'), 'description' => __('events.registration')],
        ['number' => 2, 'title' => __('events.step2_title'), 'description' => __('events.enrollment')],
        ['number' => 3, 'title' => __('events.step3_title'), 'description' => __('events.payment')],
    ],
    'event' => null,
    'model' => null,
])

@php
    $namespace = $model instanceof \Domain\Federations\Models\Federation ? 'federation' : 'entity';
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-4">
            <a href="{{ route($namespace . '.evt-events.events.show', $event) }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-primary bg-primary-50 hover:bg-primary-100 rounded-lg transition-colors duration-150"
                title="{{ $event->name }}">
                <x-heroicon-m-arrow-left class="w-4 h-4" />
                <span class="hidden sm:inline">{{ __('events.back_to_event', ['event' => Str::limit($event->name, 20)]) }}</span>
                <span class="sm:hidden">{{ __('events.event') }}</span>
            </a>
            <h2 class="text-lg font-semibold text-gray-900">{{ __('events.registration_wizard') }}</h2>
        </div>
        <span class="text-sm text-gray-500">{{ __('events.step_x_of_y', ['current' => $currentStep, 'total' => count($steps)]) }}</span>
    </div>

    <nav aria-label="Progress">
        <ol role="list" class="flex items-center justify-between">
            @foreach ($steps as $index => $step)
                @php
                    $stepNumber = $step['number'];
                    $isCompleted = $currentStep > $stepNumber;
                    $isCurrent = $currentStep === $stepNumber;
                    $isLast = $index === count($steps) - 1;
                @endphp

                <li class="relative flex items-center {{ !$isLast ? 'flex-1' : '' }}">
                    {{-- Step circle and label --}}
                    <div class="flex items-center">
                        @if ($isCompleted)
                            <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary-600">
                                <x-heroicon-s-check class="h-5 w-5 text-white" />
                            </span>
                        @elseif ($isCurrent)
                            <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-2 border-primary-600 bg-white">
                                <span class="text-sm font-medium text-primary-600">{{ $stepNumber }}</span>
                            </span>
                        @else
                            <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-2 border-gray-300 bg-white">
                                <span class="text-sm font-medium text-gray-500">{{ $stepNumber }}</span>
                            </span>
                        @endif

                        <span class="ml-3 hidden sm:flex min-w-0 flex-col">
                            <span class="text-sm font-medium {{ $isCurrent ? 'text-primary-600' : ($isCompleted ? 'text-gray-900' : 'text-gray-500') }}">
                                {{ $step['title'] }}
                            </span>
                            @if (isset($step['description']))
                                <span class="text-xs text-gray-500">{{ $step['description'] }}</span>
                            @endif
                        </span>
                    </div>

                    {{-- Connector line (not on last step) --}}
                    @if (!$isLast)
                        <div class="ml-4 flex-1 h-0.5 {{ $isCompleted ? 'bg-primary-600' : 'bg-gray-200' }}"></div>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
</div>
