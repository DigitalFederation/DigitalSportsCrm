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
use Domain\EvtEvents\Models\EventRole;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class DelegateEnrollments extends Component implements HasForms, HasTable
{
    use HasEnrollmentTableHelpers;
    use InteractsWithForms;
    use InteractsWithTable;

    public Event $event;

    public string $activeTab = 'athletes';

    protected $listeners = ['enrollmentUpdated' => '$refresh'];

    public function mount(Event $event): void
    {
        $this->authorizeTechnicalDelegate($event);
        $this->event = $event;
    }

    protected function authorizeTechnicalDelegate(Event $event): void
    {
        $user = Auth::user();

        abort_unless($user->individual, 403, __('events.no_individual_profile'));

        $hasRole = EventRole::where('event_id', $event->id)
            ->where('individual_id', $user->individual->id)
            ->where('role', EventRole::ROLE_TECHNICAL_DELEGATE)
            ->exists();

        abort_unless($hasRole, 403, __('events.not_technical_delegate'));
    }

    protected function getEnrollmentQueryForType(string $enrollmentType): ?Builder
    {
        return match ($enrollmentType) {
            'athletes' => $this->getAthletesQuery(),
            'coaches' => $this->getCoachesQuery(),
            'officials' => $this->getOfficialsQuery(),
            'referees' => $this->getRefereesQuery(),
            'staff' => $this->getStaffQuery(),
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
            'staff' => $this->staffTable($table),
            default => $this->athletesTable($table),
        };
    }

    protected function athletesTable(Table $table): Table
    {
        $attributeColumns = $this->buildNonAthleteAttributeColumns('athletes');

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

                TextColumn::make('entity.name')
                    ->label(__('events.entity'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status_class')
                    ->label(__('events.enrollment_status'))
                    ->formatStateUsing(fn ($state): string => $this->getEnrollmentStatusLabel($state instanceof EvtAthleteEnrollmentStatusEnum ? $state->value : $state))
                    ->badge()
                    ->color(fn ($state): string => $this->getEnrollmentStatusColor($state instanceof EvtAthleteEnrollmentStatusEnum ? $state->value : $state)),

                TextColumn::make('discipline.name')
                    ->label(__('events.discipline'))
                    ->sortable()
                    ->badge()
                    ->color('primary'),

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
            ->defaultSort('discipline.name', 'asc')
            ->striped()
            ->paginated([25, 50, 100]);
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

                TextColumn::make('entity.name')
                    ->label(__('events.entity'))
                    ->sortable(),

                TextColumn::make('status_class')
                    ->label(__('events.enrollment_status'))
                    ->formatStateUsing(fn ($state): string => $this->getStaffStatusLabel($state))
                    ->badge()
                    ->color(fn ($state): string => $this->getStaffStatusColor($state)),

                ...$attributeColumns,
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

                TextColumn::make('entity.name')
                    ->label(__('events.entity'))
                    ->sortable(),

                TextColumn::make('status_class')
                    ->label(__('events.enrollment_status'))
                    ->formatStateUsing(fn ($state): string => $this->getStaffStatusLabel($state))
                    ->badge()
                    ->color(fn ($state): string => $this->getStaffStatusColor($state)),

                ...$attributeColumns,
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

                ...$attributeColumns,
            ])
            ->defaultSort('individual.full_name', 'asc')
            ->striped()
            ->paginated([25, 50, 100]);
    }

    protected function staffTable(Table $table): Table
    {
        $attributeColumns = $this->buildNonAthleteAttributeColumns('staff');

        return $table
            ->query($this->getStaffQuery())
            ->columns([
                TextColumn::make('individual.full_name')
                    ->label(__('events.name'))
                    ->sortable(['first_name', 'last_name'])
                    ->searchable(['first_name', 'last_name'])
                    ->weight('medium'),

                TextColumn::make('individual.birthdate')
                    ->label(__('events.birth_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                $this->makeGenderColumn(),

                ...$attributeColumns,
            ])
            ->defaultSort('individual.full_name', 'asc')
            ->striped()
            ->paginated([25, 50, 100]);
    }

    protected function getAthletesQuery(): Builder
    {
        return AthleteEnrollment::query()
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
    }

    protected function getCoachesQuery(): Builder
    {
        return CoachEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where(function ($q) {
                $q->where('status_class', 'like', '%RegisteredCoachEnrollmentState')
                    ->orWhere('status_class', 'like', '%AssignedCoachEnrollmentState');
            })
            ->whereHas('enrollment', fn ($q) => $q->where('payment_status', EvtEventPaymentStatusEnum::PAID->value))
            ->with([
                'individual:id,name,surname,member_code,member_number,gender,birthdate',
                'entity:id,name',
                'attributes.attribute',
            ]);
    }

    protected function getOfficialsQuery(): Builder
    {
        return TeamOfficialEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where(function ($q) {
                $q->where('status_class', 'like', '%RegisteredTeamOfficialEnrollmentState')
                    ->orWhere('status_class', 'like', '%AssignedTeamOfficialEnrollmentState');
            })
            ->whereHas('enrollment', fn ($q) => $q->where('payment_status', EvtEventPaymentStatusEnum::PAID->value))
            ->with([
                'individual:id,name,surname,member_code,member_number,gender,birthdate',
                'entity:id,name',
                'attributes.attribute',
            ]);
    }

    protected function getRefereesQuery(): Builder
    {
        return RefereeEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where(function ($q) {
                $q->where('status_class', 'like', '%PendingRefereeEnrollmentState')
                    ->orWhere('status_class', 'like', '%ActiveRefereeEnrollmentState');
            })
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
        return $this->getRefereesQuery()->count();
    }

    public function getStaffCountProperty(): int
    {
        return $this->getStaffQuery()->count();
    }

    public function getTotalCountProperty(): int
    {
        return $this->athletesCount + $this->coachesCount + $this->officialsCount + $this->refereesCount + $this->staffCount;
    }

    public function exportToExcel(string $type = 'all')
    {
        $federation = Federation::first();

        $export = new ConfirmedEnrollmentsExport(
            $this->event,
            $federation,
            true,
            $type
        );

        $filename = "delegate_enrollments_{$type}_{$this->event->id}.xlsx";

        return Excel::download($export, $filename);
    }

    public function generatePdf()
    {
        $this->event->loadMissing('organizer.organizable');

        $data = [
            'event' => $this->event,
            'model' => Federation::first(),
            'isFederation' => true,
            'athletes' => $this->getAthletesQuery()->get(),
            'coaches' => $this->getCoachesQuery()->get(),
            'officials' => $this->getOfficialsQuery()->get(),
            'referees' => $this->getRefereesQuery()->get(),
            'staff' => $this->getStaffQuery()->get(),
            'generatedAt' => now(),
        ];

        $pdf = Pdf::loadView('pdf.evt-events.confirmed-enrollments', $data);

        $filename = "delegate_enrollments_{$this->event->id}.pdf";

        Notification::make()
            ->title(__('events.pdf_generated'))
            ->success()
            ->send();

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename
        );
    }

    public function render(): View
    {
        return view('livewire.evt-events.delegate-enrollments');
    }
}
