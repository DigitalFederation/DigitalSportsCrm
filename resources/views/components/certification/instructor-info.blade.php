{{-- resources/views/components/certification/instructor-info.blade.php --}}
<div class="space-y-6">
    {{-- Main Instructor Card --}}
    @if($mainInstructor)
        <div class="bg-white rounded-lg shadow">
            {{-- Card Header --}}
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ __('certifications.instructor.course_director') }}
                    </h3>
                    <span
                        class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        {{ $instructorLevel }}
                    </span>
                </div>
            </div>

            {{-- Card Content --}}
            <div class="px-6 py-4">
                <div class="flex items-start space-x-6">
                    {{-- Instructor Profile Image --}}
                    <div class="flex-shrink-0">
                        <div class="relative h-32 w-32">
                            <x-secure-profile-image :individual="$mainInstructor" size="thumb" class="h-32 w-32 rounded-lg object-cover" />

                            {{-- QR Code Overlay --}}
                            @if($mainInstructor->qrcode_path)
                                <div class="absolute bottom-0 right-0 h-12 w-12 bg-white rounded-tl-lg shadow-lg p-1">
                                    <img
                                        src="{{ $mainInstructor->qrcode_path }}"
                                        alt="QR Code"
                                        class="h-full w-full"
                                    >
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Instructor Details --}}
                    <div class="flex-1 min-w-0">
                        <div class="space-y-4">
                            {{-- Name and Basic Info --}}
                            <div>
                                <h4 class="text-xl font-medium text-gray-900">
                                    <a href="{{ route(strtolower(auth()->user()->group->code).'.individual.show', $mainInstructor->id)}}"
                                       class="hover:text-blue-600 hover:underline"
                                       target="_blank">
                                        {{ $mainInstructor->full_name }}
                                    </a>
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ __('main.Member Code') }}: <span
                                        class="font-medium text-gray-900">{{ $mainInstructor->member_code }}</span>
                                </p>
                                @if($mainInstructor->member_number)
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ __('main.member_number') }}: <span class="font-medium text-gray-900">{{ $mainInstructor->member_number }}</span>
                                    </p>
                                @endif
                            </div>

                            {{-- Additional Info --}}
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                                @if($mainInstructor->country)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('certifications.instructor.country') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $mainInstructor->country->name }}</dd>
                                    </div>
                                @endif

                                @if($mainInstructor->email)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('certifications.instructor.email') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <a href="mailto:{{ $mainInstructor->email }}"
                                               class="text-blue-600 hover:underline">
                                                {{ $mainInstructor->email }}
                                            </a>
                                        </dd>
                                    </div>
                                @endif


                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Assistants Card --}}
    @if($showAssistants)
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ __('certifications.instructor.assistant_instructors') }}
                    <span class="ml-2 text-sm text-gray-500">
                        ({{ $assistants->count() }})
                    </span>
                </h3>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach($assistants as $assistant)
                    <div class="px-6 py-4">
                        <div class="flex items-center space-x-4">
                            {{-- Assistant Profile Image --}}
                            <div class="flex-shrink-0">
                                <x-secure-profile-image :individual="$assistant" size="thumb" class="h-16 w-16 rounded-full object-cover" />
                            </div>

                            {{-- Assistant Details --}}
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-medium text-gray-900">
                                    <a href="{{ route(strtolower(auth()->user()->group->code).'.individual.show', $assistant->id)}}"
                                       class="hover:text-blue-600 hover:underline"
                                       target="_blank">
                                        {{ $assistant->full_name }}
                                    </a>
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ __('main.Member Code') }}: {{ $assistant->member_code }}
                                </p>
                            </div>

                            {{-- Quick Actions --}}
                            <div class="flex-shrink-0">
                                @if($assistant->email)
                                    <a href="mailto:{{ $assistant->email }}"
                                       class="inline-flex items-center p-2 rounded-full text-gray-400 hover:text-gray-500">
                                        <span class="sr-only">{{ __('certifications.instructor.email') }}</span>
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
