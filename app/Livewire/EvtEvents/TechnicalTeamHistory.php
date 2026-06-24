<?php

namespace App\Livewire\EvtEvents;

use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\Models\Sport;
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

class TechnicalTeamHistory extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getBaseQuery())
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
                    ->formatStateUsing(fn (string $state): string => __('events.technical_delegate'))
                    ->badge()
                    ->color('info'),

                TextColumn::make('event.status_class')
                    ->label(__('events.event_status'))
                    ->formatStateUsing(fn ($record): string => $record->event?->state?->name() ?? '-')
                    ->badge()
                    ->color(fn ($record): string => match ($record->event?->state?->color()) {
                        'green' => 'success',
                        'red' => 'danger',
                        'blue' => 'info',
                        'yellow' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->iconButton()
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->tooltip(__('events.manage_functions'))
                    ->url(fn ($record) => route('individual.technical-delegate.enrollments', $record->event))
                    ->visible(fn ($record): bool => $record->event?->status_class !== ArchiveEventState::class),
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

    protected function getBaseQuery(): Builder
    {
        $individual = Auth::user()->individual;

        return EventRole::query()
            ->where('individual_id', $individual->id)
            ->where('role', EventRole::ROLE_TECHNICAL_DELEGATE)
            ->whereHas('event')
            ->with(['event.sport']);
    }

    protected function getSportOptions(): array
    {
        return Sport::all()->mapWithKeys(fn ($sport) => [
            $sport->id => $sport->translated_name,
        ])->toArray();
    }

    public function getSportSummaries(): Collection
    {
        $individual = Auth::user()->individual;

        $roles = EventRole::query()
            ->where('individual_id', $individual->id)
            ->where('role', EventRole::ROLE_TECHNICAL_DELEGATE)
            ->whereHas('event')
            ->with(['event.sport'])
            ->get();

        $sportData = collect();

        foreach ($roles as $role) {
            $sportId = $role->event?->sport?->id;
            $sport = $role->event?->sport;

            if (! $sportId || ! $sport) {
                continue;
            }

            if (! $sportData->has($sportId)) {
                $sportData[$sportId] = (object) [
                    'sport' => $sport,
                    'count' => 0,
                    'dates' => collect(),
                ];
            }

            $sportData[$sportId]->count++;
            $sportData[$sportId]->dates->push($role->event?->start_date);
        }

        return $sportData
            ->map(function ($data) {
                $earliestDate = $data->dates->filter()->min();

                return (object) [
                    'sport_name' => $data->sport->translated_name,
                    'total_events' => $data->count,
                    'average_evaluation' => null,
                    'total_experience_points' => null,
                    'since_year' => $earliestDate?->format('Y'),
                ];
            })
            ->sortByDesc('total_events')
            ->values();
    }

    public function getHistoryCountProperty(): int
    {
        return $this->getBaseQuery()->count();
    }

    public function render(): View
    {
        return view('livewire.evt-events.technical-team-history');
    }
}
