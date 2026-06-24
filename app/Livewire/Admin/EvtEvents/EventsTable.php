<?php

namespace App\Livewire\Admin\EvtEvents;

use App\Enums\EvtEventCategoryTypeEnum;
use App\Enums\EvtEventOrganizationCategoryEnum;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Sport;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class EventsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()->with('competition.sport')
            )
            ->columns([
                TextColumn::make('start_date')
                    ->label(__('events.date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('events.event'))
                    ->searchable()
                    ->wrap()
                    ->sortable(),
                TextColumn::make('type_display')
                    ->label(__('events.type'))
                    ->state(function (Event $record): string {
                        if ($record->event_category === 'competition' && $record->competition?->sport) {
                            $sportKey = str_replace(' ', '_', strtolower($record->competition->sport->name));

                            return __('sports.' . $sportKey);
                        }
                        if ($record->event_category === 'organization' && $record->organization_type) {
                            return EvtEventOrganizationCategoryEnum::toString($record->organization_type);
                        }

                        return '-';
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('event_category', $direction);
                    }),
                TextColumn::make('end_registration')
                    ->label(__('events.registration_deadline'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('status_class')
                    ->badge()
                    ->label(__('events.status_label'))
                    ->formatStateUsing(fn ($record): string => $record->stateName())
                    ->colors([
                        'success' => fn ($state): bool => $state === 'Domain\EvtEvents\States\ActiveEventState',
                        'warning' => fn ($state): bool => $state === 'Domain\EvtEvents\States\PreparationEventState',
                        'danger' => fn ($state): bool => $state === 'Domain\EvtEvents\States\CanceledEventState',
                    ]),
            ])
            ->defaultSort('start_date', 'asc')
            ->filters([
                SelectFilter::make('event_category')
                    ->options(EvtEventCategoryTypeEnum::toTranslatedArray())
                    ->label(__('events.form.event_category')),
                SelectFilter::make('competition_sport')
                    ->label(__('events.sport'))
                    ->options(
                        Sport::all()->mapWithKeys(fn ($sport) => [
                            $sport->id => __('sports.' . str_replace(' ', '_', strtolower($sport->name))),
                        ])->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $sportId): Builder => $query->whereHas('competition', fn ($q) => $q->where('sport_id', $sportId))
                        );
                    }),
                SelectFilter::make('organization_type')
                    ->options(EvtEventOrganizationCategoryEnum::toTranslatedArray())
                    ->label(__('events.organization_type')),
                SelectFilter::make('show_archived')
                    ->options([
                        'active' => __('events.active'),
                        'archived' => __('events.status.archived'),
                    ])
                    ->default('active')
                    ->label(__('events.event_status'))
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'active' => $query->where('status_class', '!=', 'Domain\EvtEvents\States\ArchiveEventState'),
                            'archived' => $query->where('status_class', '=', 'Domain\EvtEvents\States\ArchiveEventState'),
                            default => $query,
                        };
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Action::make('enter')
                    ->label(__('common.enter'))
                    ->url(fn (Event $record): string => route('admin.evt-events.events.show', $record->id))
                    ->button()
                    ->color('primary'),
            ])
            ->bulkActions([
                // Add bulk actions here if needed
            ])
            ->defaultPaginationPageOption(50);
    }

    public function render(): View
    {
        return view('livewire.admin.evt-events.events-table');
    }
}
