@section('title', __('events.team_official_enrollment'))

<x-layout>
    <style>
        table {
            position: relative;
        }

        table th {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        table th:nth-last-child(1) {
            position: sticky;
            right: 0;
            z-index: 10;
            background: #f8fafc
        }

        table tbody tr td:nth-last-child(1) {
            position: sticky;
            right: 0;
            z-index: 1;
            background: white;
            background-clip: padding-box;
        }

        table th:nth-last-child(1),
        table tbody tr td:nth-last-child(1) {
            z-index: 10;
        }
    </style>
    <div class="previous-layout-classes">
        <x-layout.page-header
            :title="__('events.team_official_enrollment')"
            :subtitle="$event->name"
            :actions="[
                [
                    'type' => 'link',
                    'class' => 'btn btn-info',
                    'url' => route('admin.evt-events.events.show', $event->id),
                    'text' => __('events.back_to_event'),
                ],
                [
                    'type' => 'form',
                    'class' => 'btn btn-info flex flex-row gap-x-2 items-center',
                    'url' => route('admin.evt-events.events.officials-enrollment.export', $event->id),
                    'text' => __('events.export_enrollment'),
                ],
            ]"
        />

        <x-evt_event.enrollment-navigation :event="$event" active="official.index" />

        <x-filter-form :post="route('admin.evt-events.events.officials-enrollment.index', $event->id)">
            <x-forms.filter-input-text label="{{ __('events.name') }}" name="name" />
            <x-forms.filter-input-select label="{{ __('events.gender') }}" name="gender" :options="$genders" />
            <x-forms.filter-input-select label="{{ __('events.enrolled_by') }}" name="enrolled_by" :options="$enrolledByOptions" />
            <x-forms.filter-input-select label="{{ __('events.enrollment_status') }}" name="status" :options="$statuses" />
        </x-filter-form>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            @if(!empty($enrollments) && count($enrollments) > 0)
                @php
                    $headers = [
                        ['text' => __('events.name'), 'field' => 'name'],
                        ['text' => __('events.birth_date'), 'field' => 'birth_date'],
                        ['text' => __('events.gender'), 'field' => 'gender'],
                        ['text' => __('events.member_number'), 'field' => 'member_number'],
                        ['text' => __('events.enrolled_by'), 'field' => 'enrolled_by'],
                    ];

                    foreach ($allAttributes as $attribute) {
                        $headers[] = ['text' => $attribute, 'field' => Str::slug($attribute, '_')];
                    }

                    $headers[] = ['text' => __('events.enrollment_status'), 'field' => 'status_class'];
                    $headers[] = ['text' => '', 'field' => 'actions'];
                @endphp

                <x-dynamic-table :headers="$headers">
                    @foreach($enrollments as $enrollment)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">
                                <div class="font-medium text-slate-800">
                                    {{ $enrollment->individual?->name }} {{ $enrollment->individual?->surname }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->individual?->birthdate?->format('d/m/Y') }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ strtoupper(substr($enrollment->individual?->gender ?? '', 0, 1)) }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->individual?->member_number }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                                <div class="inline-flex gap-2 items-center">
                                    @php
                                        $url = "#";
                                        $enrolledBy = "";
                                        if($enrollment->enrollment->enrollable_type === 'Domain\Federations\Models\Federation'){
                                            $url = route('admin.federation.show', $enrollment->enrollment->enrollable);
                                            $enrolledBy = $enrollment->enrollment->enrollable->name;
                                        }elseif($enrollment->enrollment->enrollable_type === 'Domain\Entities\Models\Entity'){
                                            $url = route('admin.entity.show', $enrollment->enrollment->enrollable);
                                            $enrolledBy = $enrollment->enrollment->enrollable->name ?? __('events.unknown');
                                        }elseif($enrollment->enrollment->enrollable_type === 'Domain\Individuals\Models\Individual'){
                                            if(!empty($enrollment->enrollment->enrollable)){
                                                $url = route('admin.individual.show', ['individual' => $enrollment->enrollment->enrollable]);
                                                $enrolledBy = $enrollment->enrollment->enrollable->name . ' ' . $enrollment->enrollment->enrollable->surname;
                                            }
                                        }
                                    @endphp

                                    @if($url !== "#" && $enrolledBy)
                                        <a href="{{ $url }}" class="underline hover:text-slate-400">
                                        {{ $enrolledBy ?: ucwords(\Filament\Support\get_model_label($enrollment->enrollment->enrollable_type)) }}
                                        </a>
                                    @else
                                    <span>{{ ucwords(\Filament\Support\get_model_label($enrollment->enrollment->enrollable_type)) }}</span>
                                    @endif
                                </div>
                            </td>

                            @foreach ($allAttributes as $attribute)
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    @php
                                        $attributeValue = optional($enrollment->attributes->firstWhere('attribute.name', $attribute))->value ?? '';
                                    @endphp
                                    {{ $attributeValue }}
                                </td>
                            @endforeach

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full {{ $enrollment->stateColor() }}">
                                    {{ $enrollment->stateName() }}
                                </span>
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px !bg-slate-50">
                                <div class="flex flex-row items-center gap-2">
                                    @if (!$enrollment->state->isCanceled())
                                        <form
                                            action="{{ route('admin.evt-events.events.officials-enrollment.destroy', ['event' => $event, 'officials_enrollment' => $enrollment]) }}"
                                            method="POST"
                                            x-data="{
                                                submitForm(e) {
                                                    e.preventDefault();
                                                    if (confirm('{{ __('events.confirm_cancel_enrollment') }}')) {
                                                        this.$refs.form.submit();
                                                    }
                                                }
                                            }"
                                            x-ref="form"
                                            @submit="submitForm">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-500 hover:text-red-700 transition-colors duration-150 p-1 rounded-full hover:bg-red-50"
                                                title="{{ __('events.cancel_enrollment') }}">
                                                <x-svg.trash class="w-5 h-5" />
                                            </button>
                                        </form>
                                    @endif
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
