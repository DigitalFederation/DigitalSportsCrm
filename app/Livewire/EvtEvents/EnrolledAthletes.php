<?php

namespace App\Livewire\EvtEvents;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Exports\EnrolledAthletesExport;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class EnrolledAthletes extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public Event $event;
    public $enrollments;
    public bool $isEntity = false;
    /**
     * We'll store any dynamic columns generated for discipline attributes here.
     */
    protected array $attributeColumns = [];
    protected $listeners = ['enrollmentUpdated' => '$refresh'];
    public $model;

    public function mount(Event $event): void
    {
        $this->event = $event->load([
            'competitions.disciplines.attributes',
            'athleteEnrollments.individual',
            'athleteEnrollments.discipline',
            'athleteEnrollments.attributes.attribute',
        ]);

        $this->model = auth()->user()->federations()->first()
            ?? auth()->user()->entities()->first();

        // 2) Find all athlete enrollments for the current user's Federation/Entity
        //    and eager load their attributes.
        $enrollments = AthleteEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where($this->getOwnershipCondition())    // your own method
            ->with(['attributes.attribute', 'discipline'])           // the pivot + actual attribute
            ->get();

        $this->enrollments = $enrollments;

        // 3) Build a unique list of attributes from these enrollments
        $allAttributes = $enrollments
            ->flatMap(fn ($enrollment) => $enrollment->attributes->pluck('attribute'))
            ->filter()                 // remove null if any
            ->unique('id')
            ->values();

        // 4) Generate columns from $allAttributes
        $this->generateAttributeColumns($allAttributes);
    }

    /**
     * This method collects all possible attributes from the event's disciplines
     * and constructs Filament table columns for them.
     */
    protected function generateAttributeColumns($allAttributes): void
    {
        $this->attributeColumns = [];

        foreach ($allAttributes as $attribute) {
            $attrId = $attribute->id;
            $attrName = $attribute->name;

            $this->attributeColumns[] = TextColumn::make('attr_' . $attrId)
                ->label($attrName)
                ->getStateUsing(function (AthleteEnrollment $record) use ($attrId) {
                    // Key attributes by 'attribute_id' for quick lookup
                    $mappedAttributes = $record->attributes->keyBy('attribute_id');

                    if (! $mappedAttributes->has($attrId)) {
                        logger("Missing attribute {$attrId} for enrollment ID {$record->id}");

                        return '-';
                    }

                    // Fetch the attribute value
                    $matchingAttribute = $mappedAttributes->get($attrId);

                    return $matchingAttribute->value ?? '-';
                })
                ->wrap();
        }
    }

    public function table(Table $table): Table
    {
        // Define the base columns
        $baseColumns = [
            TextColumn::make('individual.full_name')
                ->label('Name')
                ->sortable(['name', 'surname'])
                ->searchable(['name', 'surname'])
                ->description(fn ($record) => $record->individual->member_code),

            TextColumn::make('discipline.name')
                ->label('Discipline')
                ->sortable()
                ->badge(),

            TextColumn::make('individual.gender')
                ->label('Gender')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'male' => 'info',
                    'female' => 'success',
                    default => 'gray',
                }),

            TextColumn::make('created_at')
                ->label('Enrolled At')
                ->dateTime()
                ->sortable(),
        ];

        // Check if any discipline is of type 'relay'
        $hasRelayDisciplines = $this->enrollments->contains(
            fn ($enrollment) => $enrollment->discipline?->enrollment_type === 'relay'
        );

        if ($hasRelayDisciplines) {
            $baseColumns[] = TextColumn::make('team_identifier')
                ->label('Team Identifier')
                ->placeholder('Individual')
                ->badge()
                ->color('primary')
                ->sortable()
                ->toggleable();
        }

        return $table
            ->query($this->getTableQuery())
            ->columns(array_merge($baseColumns, $this->attributeColumns))
            // ->filters([])
            ->headerActions([
                Action::make('export')
                    ->label('Export to Excel')
                    ->icon('heroicon-o-document')
                    ->action(function () {
                        $export = new EnrolledAthletesExport($this->event, $this->attributeColumns);

                        return Excel::download($export, 'enrolled_athletes.xlsx');
                    }),
            ])
            ->actions([
                Action::make('remove')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->modalIconColor('danger')
                    ->modalHeading('Remove Athlete From Discipline')
                    ->modalDescription(function (AthleteEnrollment $record) {
                        $message = "You are about to remove {$record->individual->full_name} from {$record->discipline?->name}.";

                        if ($record->team_identifier) {
                            $message .= ' This will also remove all other athletes in the same relay team. ';
                        }

                        $message .= 'The athlete will remain registered for the event but will need to be assigned to a new discipline. Are you sure you want to proceed?';

                        return $message;
                    })
                    ->modalSubmitActionLabel('Yes, Remove from Discipline')
                    ->modalCancelActionLabel('No, Keep in Discipline')
                    ->action(function (AthleteEnrollment $record) {
                        DB::beginTransaction();
                        try {
                            // Check if this is a relay team enrollment
                            if ($record->team_identifier) {
                                // First verify this team belongs to the current federation/entity
                                $firstTeamMember = AthleteEnrollment::where('team_identifier', $record->team_identifier)
                                    ->where('event_id', $record->event_id)
                                    ->first();

                                if (
                                    ! $firstTeamMember ||
                                    ($this->model instanceof Federation && $firstTeamMember->federation_id !== $this->model->id) ||
                                    ($this->model instanceof Entity && $firstTeamMember->entity_id !== $this->model->id)
                                ) {
                                    throw new \Exception('Unauthorized: Cannot remove team members from another federation/entity');
                                }

                                // If authorized, update all athletes in the same relay team
                                AthleteEnrollment::where('team_identifier', $record->team_identifier)
                                    ->where('event_id', $record->event_id)
                                    ->where($this->model instanceof Federation ? 'federation_id' : 'entity_id', $this->model->id)
                                    ->each(function ($teamMember) {
                                        $teamMember->update([
                                            'discipline_id' => null,
                                            'team_identifier' => null,
                                            'status_class' => EvtAthleteEnrollmentStatusEnum::PAID->value,
                                        ]);
                                        $teamMember->attributes()->delete();
                                    });
                            } else {
                                // Regular single athlete update
                                $record->update([
                                    'discipline_id' => null,
                                    'status_class' => EvtAthleteEnrollmentStatusEnum::PAID->value,
                                ]);
                                $record->attributes()->delete();
                            }

                            DB::commit();

                            Notification::make()
                                ->title('Athlete Removed from Discipline')
                                ->body('The athlete remains registered for the event and can be assigned to a new discipline.')
                                ->success()
                                ->send();

                            $this->dispatch('enrollmentUpdated');
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title('Error Removing Athlete')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->tooltip('Remove Athlete from Discipline'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([50, 100, 200]);
    }

    protected function getTableQuery(): Builder
    {
        $tableQuery = AthleteEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where($this->getOwnershipCondition())
            ->whereNotNull('discipline_id')
            ->with([
                'attributes.attribute',
                'individual:id,name,surname,member_code,gender',
                'discipline:id,name',
            ]);

        return $tableQuery;
    }

    protected function getOwnershipCondition(): array
    {
        $model = auth()->user()->federations()->first()
            ?? auth()->user()->entities()->first();

        $column = $model instanceof Federation ? 'federation_id' : 'entity_id';

        return [$column => $model->id];
    }

    public function getPendingAssignmentCount(): int
    {
        return AthleteEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where($this->getOwnershipCondition())
            ->whereNull('discipline_id')
            ->count();
    }

    public function getPreRegisteredCount(): int
    {
        return $this->getTableQuery()->count();
    }

    public function getWithDisciplineCount(): int
    {
        return $this->getTableQuery()
            ->whereNotNull('discipline_id')
            ->count();
    }

    public function render(): View
    {
        return view('livewire.evt-events.enrolled-athletes');
    }
}
