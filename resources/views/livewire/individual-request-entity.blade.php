<div>
    <form action="{{ route('individual.entity.store') }}" method="POST">
        @csrf
        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 dark:text-white">{{ __('entities.request_to_join_entity') }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('entities.request_to_join_entity_subtitle') }}</p>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <div class="mb-4 flex items-start gap-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 px-4 py-3 text-sm text-blue-700 dark:text-blue-300">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ __('entities.only_active_affiliation_entities') }}</span>
                </div>

                <div class="flex flex-col gap-4">
                    <div>
                        <label for="district_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('entities.select_district') }}
                        </label>
                        <select
                            id="district_id"
                            wire:model.live="districtId"
                            class="form-select w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="" hidden selected>{{ __('entities.choose_district') }}</option>
                            @foreach($this->districts as $district)
                                <option value="{{ $district->id }}">{{ $district->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($this->districtId)
                        @if($this->entities->isNotEmpty())
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="flex-1">
                                    <label for="entity_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                        {{ __('entities.select_entity') }}
                                    </label>
                                    <select
                                        name="entity_id"
                                        id="entity_id"
                                        wire:model.live="entitySelected"
                                        class="form-select w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="" hidden selected>{{ __('entities.choose_entity') }}</option>
                                        @foreach($this->entities as $entity)
                                            @php
                                                $individualEntity = $entity->individualEntities->first();
                                                $hasPendingInvitation = $individualEntity &&
                                                    $individualEntity->status_class === \Domain\Individuals\States\PendingFromEntityIndividualEntityState::class;
                                            @endphp
                                            <option
                                                value="{{ $entity->id }}"
                                                @if($hasPendingInvitation) disabled @endif
                                            >
                                                {{ $entity->name }}
                                                @if($hasPendingInvitation)
                                                    - {{ __('main.pending_invitation') }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('entity_id'))
                                        <p class="mt-1 text-sm text-rose-500">{{ $errors->first('entity_id') }}</p>
                                    @endif
                                </div>

                                <div class="sm:self-end">
                                    <button
                                        type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                        {{ __('entities.submit_request') }}
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-6">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-700 mb-3">
                                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('entities.no_entities_available') }}</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>
