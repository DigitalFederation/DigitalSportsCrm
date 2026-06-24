<?php

namespace App\Livewire\EvtEvents;

use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Sport;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class CoachHistory extends Component implements HasForms, HasTable
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

                TextColumn::make('entity.name')
                    ->label(__('events.entity'))
                    ->placeholder('-'),

                TextColumn::make('enrolled_function')
                    ->label(__('events.enrolled_function'))
                    ->getStateUsing(function ($record) {
                        $selectAttribute = $record->attributes
                            ->first(fn ($attr) => $attr->attribute && $attr->attribute->attribute_type === 'SELECT');

                        return $selectAttribute?->value ?: '-';
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === '-' ? 'gray' : 'primary'),
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

        return CoachEnrollment::query()
            ->where('individual_id', $individual->id)
            ->where('status_class', RegisteredCoachEnrollmentState::class)
            ->whereHas('event')
            ->with(['event.sport', 'entity', 'attributes.attribute']);
    }

    protected function getSportOptions(): array
    {
        return Sport::all()->mapWithKeys(fn ($sport) => [
            $sport->id => $sport->translated_name,
        ])->toArray();
    }

    public function getHistoryCountProperty(): int
    {
        return $this->getBaseQuery()->count();
    }

    public function render(): View
    {
        return view('livewire.evt-events.coach-history');
    }
}
