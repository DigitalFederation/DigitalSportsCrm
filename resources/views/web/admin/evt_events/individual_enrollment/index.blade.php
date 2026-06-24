@section('title', __('Event Enrollments'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0 flex flex-col">
                <h1 class="page-first-title">
                    @if ($event->name)
                        {{ $event->name }}
                    @endif
                </h1>
                <div class="text-slate-700 text-lg">
                    {{ __('Members Enrollment') }}
                </div>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2 items-start">

                <a href="{{ route('admin.evt-events.events.index') }}" class="btn btn-info">
                    {{ __('Back') }}
                </a>
                <!-- Add this line for the Export button -->
                <form action="{{ route('admin.evt-events.events.enrollments.individual.export', $event->id) }}"
                    method="POST">
                    @csrf
                    <button type="submit" class="btn btn-info flex flex-row gap-x-2 items-center">
                        <x-svg.box-arrow-down class="w-4 h-4 text-slate-400" />
                        <span>{{ __('Export Enrollment') }}</span>
                    </button>
                </form>

            </div>
        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @if (!empty($enrollments) && count($enrollments) > 0)
                @php
                    $headers = [__('Name'), __('certifications.member_code'), __('Gender'), __('Date of Birth'), __('Enrolled By')];
                    $uniqueAttributes = $enrollments
                        ->pluck('attributes')
                        ->flatten(1)
                        ->pluck('attribute')
                        ->unique('id')
                        ->pluck('name', 'id');
                    // Add the headers for the unique attributes
                    $headers = array_merge($headers, $uniqueAttributes->toArray());
                    // Add status, document, and actions to last column positions
                    $headers[] = __('Payment Status');
                    $headers[] = __('Payment Document');
                    $headers[] = __('Actions');
                @endphp
                <x-dynamic-table :headers="$headers">
                    @foreach ($enrollments as $enrollment)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="inline-flex gap-2 items-center">
                                    {{ $enrollment->individual?->name }} {{ $enrollment->individual?->surname }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->individual?->member_code }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->individual?->gender }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ date('d/m/Y', strtotime($enrollment->individual->birthdate)) }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="inline-flex gap-2 items-center">

                                    @if ($enrollment->federation_id)
                                        <a href="{{ route('admin.federation.show', $enrollment->federation_id) }}"
                                            class="underline hover:text-slate-400">
                                            {{ $enrollment->federation?->member_code }}
                                        </a>
                                    @elseif($enrollment->individual_id)
                                        <a href="{{ route('admin.individual.show', $enrollment->individual_id) }}"
                                            class="underline hover:text-slate-400">
                                            {{ __('Individual') }}
                                        </a>
                                    @endif

                                </div>
                            </td>

                            @foreach ($uniqueAttributes as $attributeId => $attributeName)
                                @php
                                    $attributeValue =
                                        $enrollment->attributes->where('attribute_id', $attributeId)->first()?->value ??
                                        'N/A';
                                @endphp
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $attributeValue }}
                                </td>
                            @endforeach

                            <!-- Payment Status Column with Dropdown -->
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <form
                                    action="{{ route('admin.evt-events.events.enrollments.individual.update-status', [$event->id, $enrollment->id]) }}"
                                    method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status"
                                        class="form-select text-sm rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        onchange="this.form.submit()">
                                        @foreach ($statusOptions as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ $enrollment->status_class === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>

                            <!-- Payment Document Column -->
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if (isset($enrollment->document) && $enrollment->document)
                                    <a href="{{ route('admin.document.show', $enrollment->document->id) }}"
                                        class="text-blue-600 hover:text-blue-800 transition-colors duration-150 underline"
                                        target="_blank">
                                        {{ $enrollment->document->getDisplayName() }}
                                        <span
                                            class="ml-1 text-xs px-2 py-1 rounded-full {{ $enrollment->document->stateColor() }}">
                                            {{ $enrollment->document->stateName() }}
                                        </span>
                                    </a>
                                @else
                                    <span class="text-gray-500">{{ __('No document') }}</span>
                                @endif
                            </td>

                            <!-- Actions Column -->
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex flex-row justify-end">
                                    <!-- Delete Button -->
                                    <form
                                        action="{{ route('admin.evt-events.events.enrollments.individual.destroy', [$event->id, $enrollment->id]) }}"
                                        method="POST" x-data="{}"
                                        @submit.prevent="if (confirm('{{ __('Are you sure you want to remove this enrollment? This action cannot be undone.') }}')) $el.submit();">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-500 hover:text-red-700 transition-colors duration-150 p-1 rounded-full hover:bg-red-50"
                                            title="{{ __('Delete enrollment') }}">

                                            <x-svg.trash class="w-5 h-5" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            @else
                <x-utility.no-data></x-utility.no-data>
            @endif
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $enrollments->links() }}
        </div>
    </div>
</x-layout>
