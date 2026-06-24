<?php

namespace App\Livewire\Entity;

use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class DivingProfessionalInviteSelectorModal extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $isOpen = false;

    public $entityId;

    public $inputId;

    public $tableLoaded = false;

    public function mount($entityId, $inputId)
    {
        $this->entityId = $entityId;
        $this->inputId = $inputId;
    }

    protected function getListeners()
    {
        return [
            "open-diving-professional-invite-modal-{$this->inputId}" => 'openModal',
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

        if ($this->entityId === null) {
            Log::warning('DivingProfessionalInviteSelectorModal called without a specific entityId.');

            return $query->whereRaw('1 = 0');
        }

        // Get the DIVINGPROFESSIONAL role ID for filtering
        $divingProfessionalRoleId = ProfessionalRole::where('role', 'DIVINGPROFESSIONAL')
            ->where('committee_id', function ($q) {
                $q->select('id')
                    ->from('committee')
                    ->where('code', 'DIVING');
            })
            ->value('id');

        // Get IDs of individuals already linked to this entity as diving professionals (active or pending)
        $excludedIndividualIds = EntityProfessionalRole::where('entity_id', $this->entityId)
            ->where('professional_role_id', $divingProfessionalRoleId)
            ->whereIn('status_class', [
                ActiveEntityProfessionalRoleState::class,
                PendingEntityProfessionalRoleState::class,
            ])
            ->pluck('individual_id')
            ->toArray();

        // Find individuals with diving certifications who are NOT already linked
        $query->where(function ($q) {
            // Has active international diving certifications
            $q->whereHas('certificationsAttributed', function ($cq) {
                $cq->certificationAttributedStatus('active')
                    ->whereHas('certification', function ($certQ) {
                        $certQ->whereHas('committee', function ($commQ) {
                            $commQ->where('code', 'DIVING');
                        });
                    });
            })
                // OR has active diving professional certifications (PADI, SSI, etc.)
                ->orWhereHas('divingProfessionalCertifications', function ($dq) {
                    $dq->active();
                })
                // OR has active international diving license
                ->orWhereHas('licenses', function ($lq) {
                    $lq->licenseAttributedStatus('active')
                        ->whereHas('license', function ($licQ) {
                            $licQ->whereHas('committee', function ($commQ) {
                                $commQ->where('code', 'DIVING');
                            });
                        });
                });
        })
            // Must have a valid member number
            ->whereNotNull('member_number')
            ->where('member_number', '!=', '')
            // Exclude already linked individuals
            ->whereNotIn('id', $excludedIndividualIds);

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
                        e($record->full_name),
                        __('common.birthdate'),
                        $birthdate,
                        __('common.nationality'),
                        e($nationality)
                    );
                }),
            Tables\Columns\TextColumn::make('member_number')
                ->label(__('common.filiation_number'))
                ->searchable(),
            Tables\Columns\TextColumn::make('certifications')
                ->label(__('diving.diving_training_systems'))
                ->getStateUsing(function (Individual $record): string {
                    $certs = [];

                    // Get international certifications
                    $hasCmasCert = $record->certificationsAttributed()
                        ->certificationAttributedStatus('active')
                        ->whereHas('certification', function ($certQ) {
                            $certQ->whereHas('committee', function ($commQ) {
                                $commQ->where('code', 'DIVING');
                            });
                        })
                        ->exists();

                    if ($hasCmasCert) {
                        $certs[] = 'CMAS';
                    }

                    // Get other diving certifications
                    $otherCerts = $record->divingProfessionalCertifications()
                        ->active()
                        ->pluck('certification_system')
                        ->unique()
                        ->toArray();

                    $certs = array_merge($certs, $otherCerts);

                    return implode(', ', $certs) ?: '-';
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
        if (empty($individual->member_number)) {
            $this->dispatch('show-error',
                message: __('diving.individual_missing_member_number', ['name' => $individual->full_name])
            );

            return;
        }

        $this->isOpen = false;
        $this->dispatch('diving-professional-invite-selected',
            code: $individual->member_number,
            name: $individual->full_name,
            inputId: $this->inputId,
        );
    }

    public function render(): View
    {
        return view('livewire.entity.diving-professional-invite-selector-modal');
    }
}
