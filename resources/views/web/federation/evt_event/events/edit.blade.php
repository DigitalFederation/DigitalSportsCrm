@section('title', __('events.manage_event_officials'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div class="mb-4 sm:mb-0 flex flex-col">
                <h1 class="page-first-title">{{ $event->name }}</h1>
                <p class="text-sm text-slate-500">{{ __('events.manage_event_officials') }}</p>
            </div>
            <div>
                <a href="{{ route('federation.evt-events.events.show', $event) }}" class="btn btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('common.back') }}
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Event Information Card (Read-only) -->
        <div class="card mb-6">
            <div class="flex justify-between items-center border-b border-gray-300 pb-2 mb-4">
                <div class="flex gap-x-2 items-center">
                    <x-svg.passport class="w-6 h-6 text-slate-600" />
                    <span class="font-bold">{{ __('events.event_information') }}</span>
                </div>
                <x-tables.badge :status="ucfirst($event->stateName())" :color="$event->stateColor()" />
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="flex flex-col">
                    <span class="text-sm text-slate-400">{{ __('events.form.start_date') }}</span>
                    <span class="text-slate-600">{{ $event->start_date?->format('d/m/Y') ?? '--' }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm text-slate-400">{{ __('events.form.end_date') }}</span>
                    <span class="text-slate-600">{{ $event->end_date?->format('d/m/Y') ?? '--' }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm text-slate-400">{{ __('events.form.event_category') }}</span>
                    <span class="text-slate-600">{{ \App\Enums\EvtEventCategoryTypeEnum::toString($event->event_category) }}</span>
                </div>
                @if($event->competition?->sport)
                    <div class="flex flex-col">
                        <span class="text-sm text-slate-400">{{ __('events.form.sport') }}</span>
                        <span class="text-slate-600">{{ $event->competition->sport->name }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Event Officials Form -->
        <form action="{{ route('federation.evt-events.events.update', $event) }}" method="POST"
              x-data="{
                  technicalDelegate: '{{ old('technical_delegate_id', $event->technicalDelegate?->individual_id) }}',
                  chiefJudge: '{{ old('chief_judge_id', $event->chiefJudge?->individual_id) }}',
                  competitionDirector: '{{ old('competition_director_id', $event->competitionDirector?->individual_id) }}',
                  technicalDelegateName: '{{ old('technical_delegate_name', $event->technicalDelegate?->individual?->full_name) }}',
                  chiefJudgeName: '{{ old('chief_judge_name', $event->chiefJudge?->individual?->full_name) }}',
                  competitionDirectorName: '{{ old('competition_director_name', $event->competitionDirector?->individual?->full_name) }}',
                  checkDuplicates() {
                      const values = [this.technicalDelegate, this.chiefJudge, this.competitionDirector].filter(v => v);
                      const unique = [...new Set(values)];
                      return values.length === unique.length;
                  }
              }"
              x-init="
                  $watch('technicalDelegate', () => { if (!checkDuplicates()) alert('{{ __('events.same_person_multiple_roles_error') }}'); });
                  $watch('chiefJudge', () => { if (!checkDuplicates()) alert('{{ __('events.same_person_multiple_roles_error') }}'); });
                  $watch('competitionDirector', () => { if (!checkDuplicates()) alert('{{ __('events.same_person_multiple_roles_error') }}'); });
              "
              x-on:individual-selected.window="
                  if ($event.detail.inputId === 'technical_delegate_selector') { technicalDelegate = $event.detail.id; technicalDelegateName = $event.detail.name; }
                  else if ($event.detail.inputId === 'chief_judge_selector') { chiefJudge = $event.detail.id; chiefJudgeName = $event.detail.name; }
                  else if ($event.detail.inputId === 'competition_director_selector') { competitionDirector = $event.detail.id; competitionDirectorName = $event.detail.name; }
              ">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
                    <x-svg.person-lines class="w-6 h-6 text-slate-600" />
                    <span class="font-bold">{{ __('events.form.management_team') }}</span>
                </div>

                <p class="text-sm text-slate-500 mb-6">{{ __('events.officials_description') }}</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Technical Delegate -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('events.technical_delegate') }}</label>
                        <div class="flex gap-2">
                            <input type="hidden" name="technical_delegate_id" :value="technicalDelegate">
                            <div class="flex-1 p-2.5 border border-slate-200 rounded bg-slate-50 text-sm truncate" x-text="technicalDelegateName || '{{ __('events.no_individual_selected') }}'"></div>
                            <x-event-individual-selector input-id="technical_delegate_selector" />
                            <button type="button" x-show="technicalDelegate" @click="technicalDelegate = ''; technicalDelegateName = ''" class="btn btn-sm btn-outline-danger">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ __('events.has_view_only_access') }}</p>
                    </div>

                    <!-- Chief Judge -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('events.chief_judge') }}</label>
                        <div class="flex gap-2">
                            <input type="hidden" name="chief_judge_id" :value="chiefJudge">
                            <div class="flex-1 p-2.5 border border-slate-200 rounded bg-slate-50 text-sm truncate" x-text="chiefJudgeName || '{{ __('events.no_individual_selected') }}'"></div>
                            <x-event-individual-selector input-id="chief_judge_selector" />
                            <button type="button" x-show="chiefJudge" @click="chiefJudge = ''; chiefJudgeName = ''" class="btn btn-sm btn-outline-danger">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ __('events.manages_post_event_functions') }}</p>
                    </div>

                    <!-- Competition Director -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('events.competition_director') }}</label>
                        <div class="flex gap-2">
                            <input type="hidden" name="competition_director_id" :value="competitionDirector">
                            <div class="flex-1 p-2.5 border border-slate-200 rounded bg-slate-50 text-sm truncate" x-text="competitionDirectorName || '{{ __('events.no_individual_selected') }}'"></div>
                            <x-event-individual-selector input-id="competition_director_selector" />
                            <button type="button" x-show="competitionDirector" @click="competitionDirector = ''; competitionDirectorName = ''" class="btn btn-sm btn-outline-danger">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ __('events.public_facing_role') }}</p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-slate-200">
                    <a href="{{ route('federation.evt-events.events.show', $event) }}" class="btn btn-secondary">
                        {{ __('common.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('common.save') }}
                    </button>
                </div>
            </div>
        </form>

    </div>
</x-layout>
