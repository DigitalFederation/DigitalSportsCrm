@section('title', __('events.staff_enrollment'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Staff List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">{{ __('events.staff_member_registration') }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ __('events.manage_staff_member_registration') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('entity.evt-events.events.show', $event) }}"
                           class="btn btn-sm btn-info">{{ __('events.back') }}</a>
                        <a href="{{ route('entity.evt-events.events.staff-enrollment.create', $event) }}"
                           class="btn btn-sm btn-primary">{{ __('events.register_staff') }}</a>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4">
                @if(!empty($enrollments) && count($enrollments) > 0)
                    <x-dynamic-table :headers="[__('events.staff_name'), __('certifications.member_code'), __('events.role'), __('events.registration_date')]">
                        @foreach($enrollments as $enrollment)
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="inline-flex gap-2 items-center">
                                        {{ $enrollment->individual?->name }} {{ $enrollment->individual?->surname }}
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ $enrollment->individual?->member_code }}
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3">
                                    @foreach($enrollment->attributes as $attribute)
                                        @php
                                            $attributeModel = $attribute->attribute;
                                            $options = $attributeModel->attribute_data ?? [];
                                            $value = $attribute->value;
                                            $label = in_array($value, $options) ? $value : ($options[$value] ?? $value);
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                            {{ $label }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ $enrollment->created_at->format('Y-m-d H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </x-dynamic-table>
                @else
                    <div class="flex flex-col justify-center items-center h-[calc(100vh-400px)]">
                        <p class="my-2 md:my-4 text-center text-gray-500 text-xl font-bold mx-auto">
                            {{ __('events.no_staff_enrollments_yet')}}
                        </p>
                        <p class="my-2 md:my-4 text-center text-gray-300 text-base prose mx-auto">
                            {{ __('events.staff_enrollment_empty_state')}}
                        </p>
                        <div class="flex items-center gap-x-2">
                            <a href="{{ route('entity.evt-events.events.staff-enrollment.create', $event) }}"
                               class="btn btn-primary mx-auto">{{ __('events.create_enrollment') }}</a>
                            @if(url()->previous() !== url()->current())
                                <a href="{{ url()->previous() }}" class="btn btn-info mx-auto">{{ __('Go Back') }}</a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$enrollments->links()}}
        </div>
    </div>
</x-layout>
