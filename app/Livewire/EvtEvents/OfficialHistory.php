<?php

namespace App\Livewire\EvtEvents;

use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\Sport;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\ArchiveEventState;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class OfficialHistory extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public string $activeTab = 'referees';

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return match ($this->activeTab) {
            'chief_judge' => $this->chiefJudgeTable($table),
            default => $this->refereesTable($table),
        };
    }

    protected function refereesTable(Table $table): Table
    {
        return $table
            ->query($this->getRefereesQuery())
            ->columns([
                TextColumn::make('event.start_date')
                    ->label(__('events.start_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('event.end_date')
                    ->label(__('events.end_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('event.name')
                    ->label(__('events.event'))
                    ->searchable()
                    ->weight('medium')
                    ->description(fn ($record): ?string => $record->event?->location),

                TextColumn::make('event.competition.sport.name')
                    ->label(__('events.sport'))
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? (__('sports.' . str_replace(' ', '_', strtolower($state))) !== 'sports.' . str_replace(' ', '_', strtolower($state))
                            ? __('sports.' . str_replace(' ', '_', strtolower($state)))
                            : $state)
                        : '-')
                    ->badge()
                    ->color('info'),

                TextColumn::make('assigned_functions')
                    ->label(__('events.functions_performed'))
                    ->getStateUsing(function ($record): string {
                        $assignments = $record->refereeFunctionAssignments;

                        if ($assignments->isEmpty()) {
                            return __('events.no_functions_assigned');
                        }

                        return $assignments->map(fn ($a) => $a->function_name)->implode(', ');
                    })
                    ->badge()
                    ->separator(',')
                    ->color(fn (string $state): string => $state === __('events.no_functions_assigned') ? 'gray' : 'primary'),

                TextColumn::make('competition_days_display')
                    ->label(__('events.competition_days'))
                    ->getStateUsing(fn ($record) => $record->refereeFunctionAssignments->first()?->competition_days ?? '-')
                    ->alignCenter(),

                TextColumn::make('number_of_games_display')
                    ->label(__('events.number_of_games'))
                    ->getStateUsing(function ($record): string {
                        $sportType = $record->event?->competition?->sport?->sport_type;

                        if ($sportType !== 'team') {
                            return __('common.not_applicable');
                        }

                        return (string) ($record->refereeFunctionAssignments->first()?->number_of_games ?? '-');
                    })
                    ->alignCenter(),

                TextColumn::make('evaluation')
                    ->label(__('events.evaluation'))
                    ->formatStateUsing(fn ($state): string => $state
                        ? $state . ' - ' . JudgeEnrollments::getEvaluationLabel((int) $state)
                        : '-')
                    ->alignCenter(),

                TextColumn::make('evaluation_notes')
                    ->label(__('events.notes'))
                    ->limit(50)
                    ->tooltip(fn ($record): ?string => $record->evaluation_notes)
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('sport')
                    ->label(__('events.sport'))
                    ->options($this->getSportOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $sportId): Builder => $query->whereHas(
                                'event.competition',
                                fn ($q) => $q->where('sport_id', $sportId)
                            )
                        );
                    }),

                Filter::make('date_range')
                    ->form([
                        DatePicker::make('start_date')
                            ->label(__('events.start_date')),
                        DatePicker::make('end_date')
                            ->label(__('events.end_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn (Builder $query, $date): Builder => $query->whereHas(
                                    'event',
                                    fn ($q) => $q->where('start_date', '>=', $date)
                                )
                            )
                            ->when(
                                $data['end_date'],
                                fn (Builder $query, $date): Builder => $query->whereHas(
                                    'event',
                                    fn ($q) => $q->where('start_date', '<=', $date)
                                )
                            );
                    })
                    ->columns(2),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->defaultSort('event.start_date', 'desc')
            ->striped()
            ->paginated([25, 50, 100]);
    }

    protected function chiefJudgeTable(Table $table): Table
    {
        return $table
            ->query($this->getChiefJudgeQuery())
            ->columns([
                TextColumn::make('event.start_date')
                    ->label(__('events.start_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('event.end_date')
                    ->label(__('events.end_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('event.name')
                    ->label(__('events.event'))
                    ->searchable()
                    ->weight('medium')
                    ->description(fn ($record): ?string => $record->event?->location),

                TextColumn::make('event.sport.name')
                    ->label(__('events.sport'))
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? (__('sports.' . str_replace(' ', '_', strtolower($state))) !== 'sports.' . str_replace(' ', '_', strtolower($state))
                            ? __('sports.' . str_replace(' ', '_', strtolower($state)))
                            : $state)
                        : '-')
                    ->badge()
                    ->color('info'),

                TextColumn::make('role')
                    ->label(__('events.function'))
                    ->formatStateUsing(fn (string $state): string => __('events.chief_judge'))
                    ->badge()
                    ->color('success'),
            ])
            ->actions([
                Action::make('evaluate')
                    ->label(__('events.evaluate'))
                    ->button()
                    ->color('primary')
                    ->extraAttributes(['class' => 'text-white'])
                    ->url(fn ($record) => route('individual.technical-delegate.referees', $record->event))
                    ->visible(fn ($record): bool => $record->event?->status_class !== ArchiveEventState::class
                        && ! $this->isEventFullyEvaluated($record)),

                Action::make('evaluated')
                    ->label(__('events.evaluated'))
                    ->button()
                    ->color('success')
                    ->extraAttributes(['class' => 'text-white pointer-events-none'])
                    ->visible(fn ($record): bool => $this->isEventFullyEvaluated($record)),
            ])
            ->filters([
                SelectFilter::make('sport')
                    ->label(__('events.sport'))
                    ->options($this->getSportOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $sportId): Builder => $query->whereHas(
                                'event.sport',
                                fn ($q) => $q->where('evt_sports.id', $sportId)
                            )
                        );
                    }),

                Filter::make('date_range')
                    ->form([
                        DatePicker::make('start_date')
                            ->label(__('events.start_date')),
                        DatePicker::make('end_date')
                            ->label(__('events.end_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn (Builder $query, $date): Builder => $query->whereHas(
                                    'event',
                                    fn ($q) => $q->where('start_date', '>=', $date)
                                )
                            )
                            ->when(
                                $data['end_date'],
                                fn (Builder $query, $date): Builder => $query->whereHas(
                                    'event',
                                    fn ($q) => $q->where('start_date', '<=', $date)
                                )
                            );
                    })
                    ->columns(2),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->defaultSort('event.start_date', 'desc')
            ->striped()
            ->paginated([25, 50, 100]);
    }

    public function getSportSummaries(): Collection
    {
        $individual = Auth::user()->individual;

        // Referee enrollments
        $refereeEnrollments = RefereeEnrollment::query()
            ->where('individual_id', $individual->id)
            ->where('status_class', ActiveRefereeEnrollmentState::class)
            ->whereHas('event')
            ->with(['event.competition.sport'])
            ->get();

        // Chief judge event roles
        $chiefJudgeRoles = EventRole::query()
            ->where('individual_id', $individual->id)
            ->where('role', EventRole::ROLE_CHIEF_JUDGE)
            ->whereHas('event')
            ->with(['event.sport'])
            ->get();

        // Build unified collection keyed by sport_id
        $sportData = collect();

        // Add referee enrollments
        foreach ($refereeEnrollments as $enrollment) {
            $sportId = $enrollment->event?->competition?->sport_id;
            $sport = $enrollment->event?->competition?->sport;

            if (! $sportId || ! $sport) {
                continue;
            }

            if (! $sportData->has($sportId)) {
                $sportData[$sportId] = (object) [
                    'sport' => $sport,
                    'referee_evaluations' => collect(),
                    'referee_dates' => collect(),
                    'referee_count' => 0,
                    'chief_judge_count' => 0,
                    'chief_judge_dates' => collect(),
                ];
            }

            $sportData[$sportId]->referee_count++;
            if ($enrollment->evaluation !== null) {
                $sportData[$sportId]->referee_evaluations->push($enrollment->evaluation);
            }
            $sportData[$sportId]->referee_dates->push($enrollment->event?->start_date);
        }

        // Add chief judge roles
        foreach ($chiefJudgeRoles as $role) {
            $sportId = $role->event?->sport?->id;
            $sport = $role->event?->sport;

            if (! $sportId || ! $sport) {
                continue;
            }

            if (! $sportData->has($sportId)) {
                $sportData[$sportId] = (object) [
                    'sport' => $sport,
                    'referee_evaluations' => collect(),
                    'referee_dates' => collect(),
                    'referee_count' => 0,
                    'chief_judge_count' => 0,
                    'chief_judge_dates' => collect(),
                ];
            }

            $sportData[$sportId]->chief_judge_count++;
            $sportData[$sportId]->chief_judge_dates->push($role->event?->start_date);
        }

        return $sportData
            ->map(function ($data) {
                $totalEvents = $data->referee_count + $data->chief_judge_count;
                $allDates = $data->referee_dates->merge($data->chief_judge_dates);
                $earliestDate = $allDates->filter()->min();

                // Experience points: referee evaluations sum + chief_judge * 10
                $refereeExp = $data->referee_evaluations->isNotEmpty() ? (int) $data->referee_evaluations->sum() : 0;
                $chiefJudgeExp = $data->chief_judge_count * 10;
                $totalExp = $refereeExp + $chiefJudgeExp;

                // Average evaluation: referee avg combined with chief_judge 5.0
                $allEvaluations = $data->referee_evaluations->toArray();
                for ($i = 0; $i < $data->chief_judge_count; $i++) {
                    $allEvaluations[] = 5.0;
                }

                $avgEvaluation = ! empty($allEvaluations) ? round(collect($allEvaluations)->avg(), 1) : null;

                return (object) [
                    'sport_name' => $data->sport->translated_name,
                    'total_events' => $totalEvents,
                    'average_evaluation' => $avgEvaluation,
                    'total_experience_points' => $totalExp > 0 ? $totalExp : null,
                    'since_year' => $earliestDate?->format('Y'),
                ];
            })
            ->filter()
            ->sortByDesc('total_events')
            ->values();
    }

    protected function getRefereesQuery(): Builder
    {
        $individual = Auth::user()->individual;

        return RefereeEnrollment::query()
            ->where('individual_id', $individual->id)
            ->where('status_class', ActiveRefereeEnrollmentState::class)
            ->whereHas('event')
            ->with([
                'event.competition.sport',
                'refereeFunctionAssignments.refereeFunction',
            ]);
    }

    protected function getChiefJudgeQuery(): Builder
    {
        $individual = Auth::user()->individual;

        return EventRole::query()
            ->where('individual_id', $individual->id)
            ->where('role', EventRole::ROLE_CHIEF_JUDGE)
            ->whereHas('event')
            ->with([
                'event.sport',
                'event.refereeEnrollments' => fn ($q) => $q
                    ->where('status_class', ActiveRefereeEnrollmentState::class)
                    ->select(['id', 'event_id', 'evaluation']),
            ]);
    }

    protected function isEventFullyEvaluated($record): bool
    {
        $referees = $record->event?->refereeEnrollments;

        if (! $referees || $referees->isEmpty()) {
            return false;
        }

        return $referees->every(fn ($enrollment) => $enrollment->evaluation !== null);
    }

    protected function getSportOptions(): array
    {
        return Sport::all()->mapWithKeys(fn ($sport) => [
            $sport->id => $sport->translated_name,
        ])->toArray();
    }

    public function getHistoryCountProperty(): int
    {
        return $this->getRefereesQuery()->count() + $this->getChiefJudgeQuery()->count();
    }

    public function getRefereesCountProperty(): int
    {
        return $this->getRefereesQuery()->count();
    }

    public function getChiefJudgeCountProperty(): int
    {
        return $this->getChiefJudgeQuery()->count();
    }

    public function render(): View
    {
        return view('livewire.evt-events.official-history', [
            'sportSummaries' => $this->getSportSummaries(),
        ]);
    }
}
