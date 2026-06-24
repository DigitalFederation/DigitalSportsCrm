<?php

namespace App\Livewire\Admin\EvtEvents;

use App\Enums\EvtCompetitionCategoryEnum;
use Domain\EvtEvents\Models\RefereeFunctionAssignment;
use Domain\EvtEvents\Models\Sport;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class TechnicalOfficialAssignmentsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RefereeFunctionAssignment::query()
                    ->with([
                        'event.competition.sport',
                        'refereeEnrollment.individual',
                        'refereeFunction',
                    ])
                    ->whereHas('event')
                    ->whereHas('refereeEnrollment.individual')
            )
            ->columns([
                TextColumn::make('refereeEnrollment.individual.full_name')
                    ->label(__('events.technical_official'))
                    ->searchable(['individual.name', 'individual.surname'])
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->refereeEnrollment?->individual?->name . ' ' . $record->refereeEnrollment?->individual?->surname),
                TextColumn::make('refereeEnrollment.individual.member_number')
                    ->label(__('events.member_number'))
                    ->sortable(),
                TextColumn::make('event.name')
                    ->label(__('events.event'))
                    ->searchable()
                    ->sortable()
                    ->grow()
                    ->extraHeaderAttributes(['style' => 'min-width: 320px'])
                    ->extraCellAttributes(['style' => 'min-width: 320px']),
                TextColumn::make('event.competition.sport.name')
                    ->label(__('events.sport'))
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? (__('sports.' . str_replace(' ', '_', strtolower($state))) !== 'sports.' . str_replace(' ', '_', strtolower($state))
                            ? __('sports.' . str_replace(' ', '_', strtolower($state)))
                            : $state)
                        : '-'),
                TextColumn::make('function_name')
                    ->label(__('events.functions_short'))
                    ->badge()
                    ->color('primary')
                    ->getStateUsing(fn ($record) => $record->refereeFunction?->function_name ?? $record->function_text ?? '-'),
                TextColumn::make('competition_days')
                    ->label(__('events.days_short'))
                    ->alignCenter()
                    ->sortable()
                    ->default('-'),
                TextColumn::make('event.competition.cat_competition')
                    ->label(__('events.category_short'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('event.start_date')
                    ->label(__('events.start_date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('number_of_games')
                    ->label(__('events.number_of_games'))
                    ->alignCenter()
                    ->sortable()
                    ->default('-')
                    ->visible(fn ($record) => in_array($record?->event?->competition?->sport_id, [4, 5])),
            ])
            ->defaultSort('event.start_date', 'desc')
            ->filters([
                Filter::make('technical_official')
                    ->form([
                        TextInput::make('name')
                            ->label(__('events.technical_official'))
                            ->placeholder(__('events.filter_by_name')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['name'],
                            fn (Builder $query, $name): Builder => $query->whereHas(
                                'refereeEnrollment.individual',
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
                            fn (Builder $query, $memberNumber): Builder => $query->whereHas(
                                'refereeEnrollment.individual',
                                fn ($q) => $q->where('member_number', 'like', "%{$memberNumber}%")
                            )
                        );
                    }),
                Filter::make('event_name')
                    ->form([
                        TextInput::make('event_name')
                            ->label(__('events.event')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['event_name'],
                            fn (Builder $query, $eventName): Builder => $query->whereHas(
                                'event',
                                fn ($q) => $q->where('name', 'like', "%{$eventName}%")
                            )
                        );
                    }),
                SelectFilter::make('sport')
                    ->label(__('events.sport'))
                    ->options(
                        Sport::orderBy('name')
                            ->pluck('name', 'id')
                            ->map(function (string $name): string {
                                $key = 'sports.' . str_replace(' ', '_', strtolower($name));

                                return __($key) !== $key ? __($key) : $name;
                            })
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $sportId): Builder => $query->whereHas(
                                'event.competition',
                                fn ($q) => $q->where('sport_id', $sportId)
                            )
                        );
                    }),
                Filter::make('assigned_function')
                    ->form([
                        TextInput::make('function')
                            ->label(__('events.assigned_functions')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['function'],
                            fn (Builder $query, $function): Builder => $query->where(function (Builder $q) use ($function) {
                                $q->where('function_text', 'like', "%{$function}%")
                                    ->orWhereHas('refereeFunction', fn ($rf) => $rf->where('function_name', 'like', "%{$function}%"));
                            })
                        );
                    }),
                Filter::make('competition_days')
                    ->form([
                        TextInput::make('days')
                            ->label(__('events.competition_days'))
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['days'],
                            fn (Builder $query, $days): Builder => $query->where('competition_days', $days)
                        );
                    }),
                SelectFilter::make('cat_competition')
                    ->label(__('events.event_category'))
                    ->options(collect(EvtCompetitionCategoryEnum::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $category): Builder => $query->whereHas(
                                'event.competition',
                                fn ($q) => $q->where('cat_competition', $category)
                            )
                        );
                    }),
                Filter::make('start_date')
                    ->form([
                        DatePicker::make('from')
                            ->label(__('events.start_date')),
                        DatePicker::make('until')
                            ->label(__('events.end_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereHas(
                                    'event',
                                    fn ($q) => $q->whereDate('start_date', '>=', $date)
                                )
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereHas(
                                    'event',
                                    fn ($q) => $q->whereDate('start_date', '<=', $date)
                                )
                            );
                    })
                    ->columns(2),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->defaultPaginationPageOption(50);
    }

    public function render(): View
    {
        return view('livewire.admin.evt-events.technical-official-assignments-table');
    }
}
