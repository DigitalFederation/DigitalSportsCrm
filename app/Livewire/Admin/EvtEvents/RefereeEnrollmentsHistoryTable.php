<?php

namespace App\Livewire\Admin\EvtEvents;

use App\Livewire\EvtEvents\JudgeEnrollments;
use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\Sport;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
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
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;

class RefereeEnrollmentsHistoryTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public string $activeTab = 'history';

    public string $evalSportFilter = '';

    public string $evalNameFilter = '';

    public string $evalExpMin = '';

    public string $evalExpMax = '';

    public string $evalLevelMin = '';

    public string $evalLevelMax = '';

    public string $evalSortBy = 'experience_points';

    public string $evalSortDir = 'desc';

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;

        if ($tab === 'history') {
            $this->resetTable();
        }
    }

    public function sortEvaluation(string $column): void
    {
        if ($this->evalSortBy === $column) {
            $this->evalSortDir = $this->evalSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->evalSortBy = $column;
            $this->evalSortDir = 'desc';
        }
    }

    public function table(Table $table): Table
    {
        return $this->historyTable($table);
    }

    protected function historyTable(Table $table): Table
    {
        return $table
            ->query(
                RefereeEnrollment::query()
                    ->where('status_class', ActiveRefereeEnrollmentState::class)
                    ->whereHas('event')
                    ->whereHas('individual')
                    ->with([
                        'event.competition.sport',
                        'individual',
                        'refereeFunctionAssignments',
                    ])
            )
            ->columns([
                TextColumn::make('event.competition.sport.name')
                    ->label(__('events.sport'))
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? (__('sports.' . str_replace(' ', '_', strtolower($state))) !== 'sports.' . str_replace(' ', '_', strtolower($state))
                            ? __('sports.' . str_replace(' ', '_', strtolower($state)))
                            : $state)
                        : '-')
                    ->badge()
                    ->color('info'),

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
                    ->weight('medium'),

                TextColumn::make('individual.full_name')
                    ->label(__('events.technical_official'))
                    ->sortable(['name', 'surname'])
                    ->searchable(['name', 'surname']),

                TextColumn::make('individual.birthdate')
                    ->label(__('events.birth_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('individual.gender')
                    ->label(__('events.gender'))
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'male' => 'M',
                        'female' => 'F',
                        default => '-',
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'success',
                        default => 'gray',
                    })
                    ->alignCenter(),

                TextColumn::make('individual.member_number')
                    ->label(__('events.member_number'))
                    ->sortable()
                    ->searchable(),

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
            ])
            ->actions([
                Action::make('viewEvaluationNotes')
                    ->iconButton()
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->color('warning')
                    ->tooltip(__('events.evaluation_notes'))
                    ->modalHeading(fn ($record) => __('events.evaluation_notes') . ' - ' . $record->individual->full_name)
                    ->modalContent(fn ($record) => view('components.evt_event.evaluation-notes-modal', [
                        'evaluation' => $record->evaluation,
                        'evaluationNotes' => $record->evaluation_notes,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('common.close'))
                    ->modalWidth('md')
                    ->visible(fn ($record): bool => $record->evaluation_notes !== null),
            ])
            ->filters([
                SelectFilter::make('sport')
                    ->label(__('events.sport'))
                    ->options(Sport::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $sportId): Builder => $query->whereHas(
                                'event.competition',
                                fn ($q) => $q->where('sport_id', $sportId)
                            )
                        );
                    }),

                Filter::make('individual_name')
                    ->form([
                        TextInput::make('name')
                            ->label(__('events.technical_official'))
                            ->placeholder(__('events.filter_by_name')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['name'],
                            fn (Builder $query, $name): Builder => $query->whereHas(
                                'individual',
                                fn ($q) => $q->where('name', 'like', "%{$name}%")
                                    ->orWhere('surname', 'like', "%{$name}%")
                            )
                        );
                    }),

                Filter::make('member_number')
                    ->form([
                        TextInput::make('member_number')
                            ->label(__('events.member_number'))
                            ->placeholder(__('events.filter_by_member_number')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['member_number'],
                            fn (Builder $query, $number): Builder => $query->whereHas(
                                'individual',
                                fn ($q) => $q->where('member_number', 'like', "%{$number}%")
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
            ->filtersFormColumns(5)
            ->defaultSort('event.start_date', 'desc')
            ->striped()
            ->paginated([25, 50, 100]);
    }

    public function getEvaluationRanking(): Collection
    {
        $refereeEnrollments = RefereeEnrollment::query()
            ->where('status_class', ActiveRefereeEnrollmentState::class)
            ->whereHas('event')
            ->whereHas('individual')
            ->with(['event.competition.sport', 'individual'])
            ->get();

        $chiefJudgeRoles = EventRole::query()
            ->where('role', EventRole::ROLE_CHIEF_JUDGE)
            ->whereHas('event')
            ->whereHas('individual')
            ->with(['event.sport', 'individual'])
            ->get();

        $rankings = collect();

        foreach ($refereeEnrollments as $enrollment) {
            $sportId = $enrollment->event?->competition?->sport_id;
            $sport = $enrollment->event?->competition?->sport;
            $individualId = $enrollment->individual_id;

            if (! $sportId || ! $sport || ! $individualId) {
                continue;
            }

            $key = $individualId . '_' . $sportId;

            if (! $rankings->has($key)) {
                $rankings[$key] = (object) [
                    'individual' => $enrollment->individual,
                    'sport' => $sport,
                    'referee_evaluations' => collect(),
                    'referee_count' => 0,
                    'chief_judge_count' => 0,
                ];
            }

            $rankings[$key]->referee_count++;
            if ($enrollment->evaluation !== null) {
                $rankings[$key]->referee_evaluations->push($enrollment->evaluation);
            }
        }

        foreach ($chiefJudgeRoles as $role) {
            $sportId = $role->event?->sport?->id;
            $sport = $role->event?->sport;
            $individualId = $role->individual_id;

            if (! $sportId || ! $sport || ! $individualId) {
                continue;
            }

            $key = $individualId . '_' . $sportId;

            if (! $rankings->has($key)) {
                $rankings[$key] = (object) [
                    'individual' => $role->individual,
                    'sport' => $sport,
                    'referee_evaluations' => collect(),
                    'referee_count' => 0,
                    'chief_judge_count' => 0,
                ];
            }

            $rankings[$key]->chief_judge_count++;
        }

        $rankings = $rankings->map(function ($data) {
            $refereeExp = $data->referee_evaluations->isNotEmpty() ? (int) $data->referee_evaluations->sum() : 0;
            $chiefJudgeExp = $data->chief_judge_count * 10;
            $totalExp = $refereeExp + $chiefJudgeExp;

            $allEvaluations = $data->referee_evaluations->toArray();
            for ($i = 0; $i < $data->chief_judge_count; $i++) {
                $allEvaluations[] = 5.0;
            }

            $avgEvaluation = ! empty($allEvaluations) ? round(collect($allEvaluations)->avg(), 1) : null;

            return (object) [
                'individual_name' => $data->individual->full_name,
                'sport_name' => $data->sport->translated_name,
                'sport_id' => $data->sport->id,
                'total_events' => $data->referee_count + $data->chief_judge_count,
                'experience_points' => $totalExp,
                'average_level' => $avgEvaluation,
            ];
        })->filter(fn ($item) => $item->experience_points > 0 || $item->average_level !== null);

        // Apply filters
        if ($this->evalSportFilter !== '') {
            $rankings = $rankings->filter(fn ($item) => $item->sport_id == $this->evalSportFilter);
        }

        if ($this->evalNameFilter !== '') {
            $search = mb_strtolower($this->evalNameFilter);
            $rankings = $rankings->filter(fn ($item) => str_contains(mb_strtolower($item->individual_name), $search));
        }

        if ($this->evalExpMin !== '') {
            $rankings = $rankings->filter(fn ($item) => $item->experience_points >= (int) $this->evalExpMin);
        }

        if ($this->evalExpMax !== '') {
            $rankings = $rankings->filter(fn ($item) => $item->experience_points <= (int) $this->evalExpMax);
        }

        if ($this->evalLevelMin !== '') {
            $rankings = $rankings->filter(fn ($item) => $item->average_level !== null && $item->average_level >= (float) $this->evalLevelMin);
        }

        if ($this->evalLevelMax !== '') {
            $rankings = $rankings->filter(fn ($item) => $item->average_level !== null && $item->average_level <= (float) $this->evalLevelMax);
        }

        // Sort
        $rankings = $this->evalSortDir === 'desc'
            ? $rankings->sortByDesc($this->evalSortBy)
            : $rankings->sortBy($this->evalSortBy);

        return $rankings->values();
    }

    protected function getSportOptions(): array
    {
        return Sport::all()->mapWithKeys(fn ($sport) => [
            $sport->id => $sport->translated_name,
        ])->toArray();
    }

    public function render(): View
    {
        $data = [];

        if ($this->activeTab === 'evaluation') {
            $data['evaluationRanking'] = $this->getEvaluationRanking();
            $data['sportOptions'] = $this->getSportOptions();
        }

        return view('livewire.admin.evt-events.referee-enrollments-history-table', $data);
    }
}
