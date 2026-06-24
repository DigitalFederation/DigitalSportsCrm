<?php

namespace App\Livewire\Admin\EvtEvents;

use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Sport;
use Domain\EvtEvents\States\AssignedCoachEnrollmentState;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
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

class CoachEnrollmentsHistoryTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CoachEnrollment::query()
                    ->whereIn('status_class', [
                        RegisteredCoachEnrollmentState::class,
                        AssignedCoachEnrollmentState::class,
                    ])
                    ->whereHas('event')
                    ->whereHas('individual')
                    ->with([
                        'event.competition.sport',
                        'individual',
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
                    ->label(__('events.coach'))
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
                            ->label(__('events.coach'))
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

    public function render(): View
    {
        return view('livewire.admin.evt-events.coach-enrollments-history-table');
    }
}
