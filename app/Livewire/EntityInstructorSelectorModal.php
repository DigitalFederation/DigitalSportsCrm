<?php

namespace App\Livewire;

// Changed imports and model references from EntityAthlete to EntityProfessionalRole
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Individuals\Models\Individual;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

// Renamed class
class EntityInstructorSelectorModal extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $isOpen = false;
    public $entityId;
    public $inputId;
    public $tableLoaded = false;
    public $certificationSystems = [];

    public function mount($entityId, $inputId, $certificationSystems = [])
    {
        $this->entityId = $entityId;
        $this->inputId = $inputId;
        $this->certificationSystems = $certificationSystems;
    }

    protected function getListeners()
    {
        return [
            "open-entity-instructor-modal-{$this->inputId}" => 'openModal',
        ];
    }

    public function loadTable()
    {
        $this->tableLoaded = true;
    }
    public function openModal($payload = null)
    {
        $this->isOpen = true;
        if (! $this->tableLoaded) {
            $this->loadTable();
        }
    }

    public function getTableRecordsPerPage(): int
    {
        return 5;
    }

    protected function getTableQuery(): Builder
    {
        $query = Individual::query();

        if ($this->entityId !== null) {
            // Get individuals who are diving professionals invited by this entity
            $query->whereHas('professionalRoleEntities', function ($roleQuery) {
                $roleQuery->where('entity_id', $this->entityId)
                    ->where('status_class', \Domain\Entities\States\ActiveEntityProfessionalRoleState::class)
                    ->whereHas('professionalRole', function ($profQuery) {
                        $profQuery->where('role', 'DIVINGPROFESSIONAL')
                            ->where('committee_id', function ($q) {
                                $q->select('id')
                                    ->from('committee')
                                    ->where('code', 'DIVING');
                            });
                    });
            })
            // Filter by certification systems if provided, otherwise show all with any certifications
                ->where(function ($q) {
                    if (empty($this->certificationSystems)) {
                        // Show all individuals with any diving certifications
                        $q->whereHas('certificationsAttributed', function ($cq) {
                            $cq->certificationAttributedStatus('active');
                        })
                            ->orWhereHas('divingProfessionalCertifications', function ($dq) {
                                $dq->active();
                            });
                    } else {
                        // Filter by specific certification systems
                        foreach ($this->certificationSystems as $system) {
                            if ($system === 'CMAS') {
                                $q->orWhereHas('certificationsAttributed', function ($cq) {
                                    $cq->certificationAttributedStatus('active')
                                        ->whereHas('certification', function ($certQ) {
                                            $certQ->whereHas('committee', function ($commQ) {
                                                $commQ->where('code', 'DIVING');
                                            });
                                        });
                                });
                            } else {
                                $q->orWhereHas('divingProfessionalCertifications', function ($dq) use ($system) {
                                    $dq->active()->where('certification_system', $system);
                                });
                            }
                        }
                    }
                })
            // Must have a valid member number
                ->whereNotNull('member_number')
                ->where('member_number', '!=', '');
        } else {
            Log::warning('EntityInstructorSelectorModal called without a specific entityId.');
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('common.full_name'))
                ->searchable(['name', 'surname'])
                ->sortable(['name', 'surname'])
                ->html()
                ->getStateUsing(function (Individual $record): string {
                    $birthdate = '-';
                    if ($record->birthdate) {
                        if (is_string($record->birthdate)) {
                            $birthdate = \Carbon\Carbon::parse($record->birthdate)->format('d/m/Y');
                        } else {
                            $birthdate = $record->birthdate->format('d/m/Y');
                        }
                    }
                    $nationality = $record->country ? $record->country->name : '-';

                    return sprintf(
                        '<div class="space-y-0.5">
                            <div class="font-medium">%s</div>
                            <div class="text-xs text-gray-500">%s: %s</div>
                            <div class="text-xs text-gray-500">%s: %s</div>
                        </div>',
                        $record->full_name,
                        __('common.birthdate'),
                        $birthdate,
                        __('common.nationality'),
                        $nationality
                    );
                }),
            Tables\Columns\TextColumn::make('member_number')->label(__('common.filiation_number'))->searchable(),
            Tables\Columns\TextColumn::make('certifications')
                ->label(__('diving.diving_training_systems'))
                ->getStateUsing(function (Individual $record): string {
                    $certs = [];

                    // Get international certifications
                    if ($record->certificationsAttributed()->certificationAttributedStatus('active')->exists()) {
                        $certs[] = 'CMAS';
                    }

                    // Get other diving certifications
                    $otherCerts = $record->divingProfessionalCertifications()
                        ->active()
                        ->pluck('certification_system')
                        ->unique()
                        ->toArray();

                    $certs = array_merge($certs, $otherCerts);

                    return implode(', ', $certs) ?: 'None';
                }),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('country')
                ->relationship('country', 'name'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('select')
                ->label(__('common.select'))
                ->action(fn (Individual $record) => $this->selectIndividual($record))
                ->button(),
        ];
    }

    public function selectIndividual(Individual $individual): void
    {
        // Check if individual has a member number
        if (empty($individual->member_number)) {
            $this->dispatch('show-error',
                message: __('diving.individual_missing_member_number', ['name' => $individual->full_name])
            );

            return;
        }

        $this->isOpen = false;
        // Dispatch browser event with parameters for Alpine.js to catch
        $this->dispatch('individual-selected', [
            'code' => $individual->member_number,
            'name' => $individual->full_name,
            'inputId' => $this->inputId,
        ]);
    }

    public function render(): View
    {
        // Render the new view
        return view('livewire.entity-instructor-selector-modal');
    }
}
