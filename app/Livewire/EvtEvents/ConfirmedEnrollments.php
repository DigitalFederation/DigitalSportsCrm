<?php

namespace App\Livewire\EvtEvents;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEventPaymentStatusEnum;
use App\Exports\ConfirmedEnrollmentsExport;
use App\Livewire\EvtEvents\Concerns\HasEnrollmentTableHelpers;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\StaffEnrollment;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\Federations\Models\Federation;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ConfirmedEnrollments extends Component implements HasForms, HasTable
{
    use HasEnrollmentTableHelpers;
    use InteractsWithForms;
    use InteractsWithTable;

    public Event $event;

    public string $activeTab = 'athletes';

    public $model;

    public bool $isFederation = false;

    protected $listeners = ['enrollmentUpdated' => '$refresh'];

    public function mount(Event $event): void
    {
        $this->event = $event->load([
            'competitions.disciplines.attributes',
        ]);

        $this->model = auth()->user()->federations()->first()
            ?? auth()->user()->entities()->first();

        $this->isFederation = $this->model instanceof Federation;
    }

    protected function getEnrollmentQueryForType(string $enrollmentType): ?Builder
    {
        return match ($enrollmentType) {
            'coaches' => $this->getCoachesQuery(),
            'officials' => $this->getOfficialsQuery(),
            'referees' => $this->getRefereesQuery(),
            default => null,
        };
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return match ($this->activeTab) {
            'athletes' => $this->athletesTable($table),
            'coaches' => $this->coachesTable($table),
            'officials' => $this->officialsTable($table),
            'referees' => $this->refereesTable($table),
            default => $this->athletesTable($table),
        };
    }

    protected function athletesTable(Table $table): Table
    {
        // Build dynamic attribute columns based on event's discipline attributes
        $attributeColumns = $this->buildAttributeColumns();

        return $table
            ->query($this->getAthletesQuery())
            ->columns([
                TextColumn::make('individual.full_name')
                    ->label(__('events.athlete'))
                    ->sortable(['name', 'surname'])
                    ->searchable(['name', 'surname'])
                    ->description(fn ($record) => $record->individual->member_code)
                    ->weight('medium'),

                TextColumn::make('individual.member_number')
                    ->label(__('events.member_number'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('individual.birthdate')
                    ->label(__('events.birth_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                $this->makeGenderColumn(),

                TextColumn::make('discipline.name')
                    ->label(__('events.discipline'))
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('status_class')
                    ->label(__('events.enrollment_status'))
                    ->formatStateUsing(fn ($state): string => $this->getEnrollmentStatusLabel($state instanceof EvtAthleteEnrollmentStatusEnum ? $state->value : $state))
                    ->badge()
                    ->color(fn ($state): string => $this->getEnrollmentStatusColor($state instanceof EvtAthleteEnrollmentStatusEnum ? $state->value : $state)),

                TextColumn::make('entity.name')
                    ->label(__('events.entity'))
                    ->visible($this->isFederation)
                    ->sortable()
                    ->toggleable(),

                // Dynamic attribute columns
                ...$attributeColumns,
            ])
            ->actions([
                Action::make('viewDetails')
                    ->iconButton()
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->tooltip(__('events.details'))
                    ->modalHeading(fn ($record) => $record->individual->full_name)
                    ->modalDescription(fn ($record) => $record->discipline?->name)
                    ->modalContent(fn ($record) => view('livewire.evt-events.partials.enrollment-attributes-modal', [
                        'record' => $record,
                        'attributes' => $this->getDetailAttributes($record),
                    ]))
                    ->modalWidth('md')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('Close'))
                    ->visible(fn ($record) => $this->hasDetailAttributes($record)),
            ])
            ->filters([
                SelectFilter::make('discipline')
                    ->relationship('discipline', 'name')
                    ->label(__('events.discipline'))
                    ->multiple()
                    ->preload(),
                SelectFilter::make('enrollment_status')
                    ->label(__('events.enrollment_status'))
                    ->options([
                        'enrolled' => __('events.enrollment_status_enrolled'),
                        'confirmed' => __('events.enrollment_status_confirmed'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'enrolled' => $query->where('status_class', EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value),
                            'confirmed' => $query->where('status_class', EvtAthleteEnrollmentStatusEnum::COMPLETED->value),
                            default => $query,
                        };
                    }),
            ])
            ->headerActions([
                Action::make('export')
                    ->label(__('events.export_excel'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn () => $this->exportToExcel('athletes')),
            ])
            ->defaultSort('discipline.name', 'asc')
            ->striped()
            ->paginated([25, 50, 100]);
    }

    /**
     * Build dynamic columns for all unique attributes in the event's disciplines.
     * Skips system attributes (OUTOFRACE, HIDDEN) and global attributes (team-level like relay times).
     */
    protected function buildAttributeColumns(): array
    {
        $columns = [];
        $uniqueAttributes = $this->getUniqueAttributesFromEvent();

        // System attribute types that should not be displayed as columns
        $systemTypes = ['OUTOFRACE', 'HIDDEN'];

        foreach ($uniqueAttributes as $attribute) {
            $attrId = $attribute['id'];
            $attrType = strtoupper($attribute['type'] ?? 'TEXT');

            // Skip system attributes and global attributes (team-level)
            if (in_array($attrType, $systemTypes) || $attribute['is_global']) {
                continue;
            }

            // Create column based on attribute type
            if (in_array($attrType, ['TIME', 'BESTTIME'])) {
                // Time attributes with mono font
                $columns[] = TextColumn::make('attr_' . $attrId)
                    ->label($attribute['name'])
                    ->getStateUsing(function ($record) use ($attrId) {
                        $attr = $record->attributes->firstWhere('attribute_id', $attrId);

                        return $attr?->value ?: '-';
                    })
                    ->fontFamily('mono')
                    ->alignCenter();
            } else {
                // Other attributes as regular text
                $columns[] = TextColumn::make('attr_' . $attrId)
                    ->label($attribute['name'])
                    ->getStateUsing(function ($record) use ($attrId) {
                        $attr = $record->attributes->firstWhere('attribute_id', $attrId);

                        return $attr?->value ?: '-';
                    })
                    ->alignCenter();
            }
        }

        return $columns;
    }

    /**
     * Get all unique attributes from the event's disciplines.
     */
    protected function getUniqueAttributesFromEvent(): array
    {
        $uniqueAttributes = [];
        $seenIds = [];

        // Ensure relationships are loaded (may not be preserved between Livewire requests)
        $this->event->loadMissing('competitions.disciplines.attributes');

        foreach ($this->event->competitions as $competition) {
            foreach ($competition->disciplines as $discipline) {
                if ($discipline->relationLoaded('attributes') && $discipline->attributes->isNotEmpty()) {
                    foreach ($discipline->attributes as $attribute) {
                        if (! in_array($attribute->id, $seenIds)) {
                            $seenIds[] = $attribute->id;
                            $uniqueAttributes[] = [
                                'id' => $attribute->id,
                                'name' => $attribute->name,
                                'type' => $attribute->attribute_type ?? 'TEXT',
                                'is_global' => (bool) $attribute->fillable_global,
                            ];
                        }
                    }
                }
            }
        }

        return $uniqueAttributes;
    }

    protected function coachesTable(Table $table): Table
    {
        $attributeColumns = $this->buildNonAthleteAttributeColumns('coaches');

        return $table
            ->query($this->getCoachesQuery())
            ->columns([
                TextColumn::make('individual.full_name')
                    ->label(__('events.coach'))
                    ->sortable(['name', 'surname'])
                    ->searchable(['name', 'surname'])
                    ->description(fn ($record) => $record->individual->member_code)
                    ->weight('medium'),

                TextColumn::make('individual.member_number')
                    ->label(__('events.member_number'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('individual.birthdate')
                    ->label(__('events.birth_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                $this->makeGenderColumn(),

                TextColumn::make('status_class')
                    ->label(__('events.enrollment_status'))
                    ->formatStateUsing(fn ($state): string => $this->getStaffStatusLabel($state))
                    ->badge()
                    ->color(fn ($state): string => $this->getStaffStatusColor($state)),

                TextColumn::make('entity.name')
                    ->label(__('events.entity'))
                    ->visible($this->isFederation)
                    ->sortable(),

                // Dynamic attribute columns
                ...$attributeColumns,
            ])
            ->headerActions([
                Action::make('export')
                    ->label(__('events.export_excel'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn () => $this->exportToExcel('coaches')),
            ])
            ->defaultSort('individual.full_name', 'asc')
            ->striped()
            ->paginated([25, 50, 100]);
    }

    protected function officialsTable(Table $table): Table
    {
        $attributeColumns = $this->buildNonAthleteAttributeColumns('officials');

        return $table
            ->query($this->getOfficialsQuery())
            ->columns([
                TextColumn::make('individual.full_name')
                    ->label(__('events.official'))
                    ->sortable(['name', 'surname'])
                    ->searchable(['name', 'surname'])
                    ->description(fn ($record) => $record->individual->member_code)
                    ->weight('medium'),

                TextColumn::make('individual.member_number')
                    ->label(__('events.member_number'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('individual.birthdate')
                    ->label(__('events.birth_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                $this->makeGenderColumn(),

                TextColumn::make('status_class')
                    ->label(__('events.enrollment_status'))
                    ->formatStateUsing(fn ($state): string => $this->getStaffStatusLabel($state))
                    ->badge()
                    ->color(fn ($state): string => $this->getStaffStatusColor($state)),

                TextColumn::make('entity.name')
                    ->label(__('events.entity'))
                    ->visible($this->isFederation)
                    ->sortable(),

                // Dynamic attribute columns
                ...$attributeColumns,
            ])
            ->headerActions([
                Action::make('export')
                    ->label(__('events.export_excel'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn () => $this->exportToExcel('officials')),
            ])
            ->defaultSort('individual.full_name', 'asc')
            ->striped()
            ->paginated([25, 50, 100]);
    }

    protected function refereesTable(Table $table): Table
    {
        $attributeColumns = $this->buildNonAthleteAttributeColumns('referees');

        return $table
            ->query($this->getRefereesQuery())
            ->columns([
                TextColumn::make('individual.full_name')
                    ->label(__('events.referee'))
                    ->sortable(['name', 'surname'])
                    ->searchable(['name', 'surname'])
                    ->description(fn ($record) => $record->individual->member_code)
                    ->weight('medium'),

                TextColumn::make('individual.birthdate')
                    ->label(__('events.birth_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                $this->makeGenderColumn(),

                // Dynamic attribute columns
                ...$attributeColumns,
            ])
            ->headerActions([
                Action::make('export')
                    ->label(__('events.export_excel'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn () => $this->exportToExcel('referees')),
            ])
            ->defaultSort('individual.full_name', 'asc')
            ->striped()
            ->paginated([25, 50, 100]);
    }

    protected function getAthletesQuery(): Builder
    {
        $query = AthleteEnrollment::query()
            ->where('event_id', $this->event->id)
            ->whereIn('status_class', [
                EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
                EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
            ])
            ->with([
                'individual:id,name,surname,member_code,member_number,gender,birthdate',
                'discipline:id,name',
                'entity:id,name',
                'attributes.attribute',
            ]);

        if (! $this->isFederation) {
            $query->where('entity_id', $this->model->id);
        }

        return $query;
    }

    protected function getCoachesQuery(): Builder
    {
        $query = CoachEnrollment::query()
            ->where('event_id', $this->event->id)
            ->with([
                'individual:id,name,surname,member_code,member_number,gender,birthdate',
                'entity:id,name',
                'attributes.attribute',
            ]);

        // Filter to only active/registered coaches with confirmed parent enrollment
        $query->where(function ($q) {
            $q->where('status_class', 'like', '%RegisteredCoachEnrollmentState')
                ->orWhere('status_class', 'like', '%AssignedCoachEnrollmentState');
        });

        $query->whereHas('enrollment', fn ($q) => $q->where('payment_status', EvtEventPaymentStatusEnum::PAID->value));

        if (! $this->isFederation) {
            $query->where('entity_id', $this->model->id);
        }

        return $query;
    }

    protected function getOfficialsQuery(): Builder
    {
        $query = TeamOfficialEnrollment::query()
            ->where('event_id', $this->event->id)
            ->with([
                'individual:id,name,surname,member_code,member_number,gender,birthdate',
                'entity:id,name',
                'attributes.attribute',
            ]);

        // Filter to only active/registered officials with confirmed parent enrollment
        $query->where(function ($q) {
            $q->where('status_class', 'like', '%RegisteredTeamOfficialEnrollmentState')
                ->orWhere('status_class', 'like', '%AssignedTeamOfficialEnrollmentState');
        });

        $query->whereHas('enrollment', fn ($q) => $q->where('payment_status', EvtEventPaymentStatusEnum::PAID->value));

        if (! $this->isFederation) {
            $query->where('entity_id', $this->model->id);
        }

        return $query;
    }

    protected function getRefereesQuery(): Builder
    {
        // Referees are federation-only, no entity_id filtering needed
        return RefereeEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where(function ($q) {
                $q->where('status_class', 'like', '%ActiveRefereeEnrollmentState');
            })
            ->whereHas('enrollment', fn ($q) => $q->where('payment_status', EvtEventPaymentStatusEnum::PAID->value))
            ->with([
                'individual:id,name,surname,member_code,member_number,gender,birthdate',
                'attributes.attribute',
            ]);
    }

    protected function getStaffQuery(): Builder
    {
        return StaffEnrollment::query()
            ->where('event_id', $this->event->id)
            ->with([
                'individual:id,name,surname,member_code,member_number,gender,birthdate',
                'attributes.attribute',
            ]);
    }

    public function getAthletesCountProperty(): int
    {
        return $this->getAthletesQuery()->count();
    }

    public function getCoachesCountProperty(): int
    {
        return $this->getCoachesQuery()->count();
    }

    public function getOfficialsCountProperty(): int
    {
        return $this->getOfficialsQuery()->count();
    }

    public function getRefereesCountProperty(): int
    {
        if (! $this->isFederation) {
            return 0;
        }

        return $this->getRefereesQuery()->count();
    }

    public function getTotalCountProperty(): int
    {
        return $this->athletesCount + $this->coachesCount + $this->officialsCount + $this->refereesCount;
    }

    public function exportToExcel(string $type = 'all')
    {
        $export = new ConfirmedEnrollmentsExport(
            $this->event,
            $this->model,
            $this->isFederation,
            $type
        );

        $filename = "confirmed_enrollments_{$type}_{$this->event->id}.xlsx";

        return Excel::download($export, $filename);
    }

    public function generatePdf()
    {
        $this->event->loadMissing('organizer.organizable');

        $data = [
            'event' => $this->event,
            'model' => $this->model,
            'isFederation' => $this->isFederation,
            'athletes' => $this->getAthletesQuery()->get(),
            'coaches' => $this->getCoachesQuery()->get(),
            'officials' => $this->getOfficialsQuery()->get(),
            'referees' => $this->isFederation ? $this->getRefereesQuery()->get() : collect(),
            'staff' => $this->isFederation ? $this->getStaffQuery()->get() : collect(),
            'generatedAt' => now(),
        ];

        $pdf = Pdf::loadView('pdf.evt-events.confirmed-enrollments', $data);

        $filename = "confirmed_enrollments_{$this->event->id}.pdf";

        Notification::make()
            ->title(__('events.pdf_generated'))
            ->success()
            ->send();

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename
        );
    }

    public function getStep1RouteProperty(): string
    {
        return $this->isFederation
            ? route('federation.evt-events.events.enrollments.create', ['event' => $this->event, 'type' => 'athlete'])
            : route('entity.evt-events.events.enrollments.create', ['event' => $this->event, 'type' => 'athlete']);
    }

    public function getStep2RouteProperty(): string
    {
        return $this->isFederation
            ? route('federation.evt-events.events.review', ['event' => $this->event])
            : route('entity.evt-events.events.review', ['event' => $this->event]);
    }

    public function render(): View
    {
        return view('livewire.evt-events.confirmed-enrollments');
    }
}
