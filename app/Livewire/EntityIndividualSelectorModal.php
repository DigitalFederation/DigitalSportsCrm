<?php

namespace App\Livewire;

use Domain\Entities\Models\EntityAthlete;
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

class EntityIndividualSelectorModal extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $isOpen = false;
    public $entityId;
    public $inputId;
    public $tableLoaded = false;

    protected $listeners = ['open-entity-modal' => 'openModal'];

    public function mount($entityId, $inputId)
    {
        $this->entityId = $entityId;
        $this->inputId = $inputId;
    }

    public function loadTable()
    {
        $this->tableLoaded = true;
    }
    public function openModal($inputIdToCheck = null)
    {
        // Ensure the modal only opens if the inputId matches
        if ($inputIdToCheck === null || $inputIdToCheck === $this->inputId) {
            $this->isOpen = true;
        }
    }

    public function getTableRecordsPerPage(): int
    {
        return 5;
    }

    protected function getTableQuery(): Builder
    {

        $query = Individual::query()
            ->with(['entityAthletes' => fn ($q) => $q->where('entity_id', $this->entityId)]);

        if ($this->entityId !== null) {
            // Filter individuals that are linked to the specific entity via EntityAthlete
            $query->whereHas('entityAthletes', function ($subQuery) {
                return $subQuery->where('entity_id', $this->entityId);
            });
        } else {
            // If no entityId is provided, log a warning and return an empty query
            // to avoid showing all individuals.
            Log::warning('EntityIndividualSelectorModal called without a specific entityId.');
            $query->whereRaw('1 = 0'); // Ensures no records are returned
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Full Name')
                ->searchable(['name', 'surname'])
                ->sortable(['name', 'surname'])
                ->getStateUsing(fn (Individual $record): string => $record->full_name),
            Tables\Columns\TextColumn::make('member_code')->label('International Code')->searchable(),
            Tables\Columns\TextColumn::make('country.name')
                ->label('Nationality')
                ->sortable()
                ->searchable(isGlobal: false),
            Tables\Columns\TextColumn::make('birthdate')->label('Birthdate')->date(),
            Tables\Columns\TextColumn::make('national_code_display')
                ->label('National Code')
                ->searchable(isGlobal: false)
                ->getStateUsing(function (Individual $record) {
                    // Find the specific EntityAthlete record for this entity
                    $entityAthlete = $record->entityAthletes->where('entity_id', $this->entityId)->first();

                    return $entityAthlete?->national_code ?? 'N/A';
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
                ->label('Select')
                ->action(fn (Individual $record) => $this->selectIndividual($record))
                ->button(),
        ];
    }

    public function selectIndividual(Individual $individual): void
    {
        $this->isOpen = false;
        $this->dispatch('individual-selected', code: $individual->member_code, inputId: $this->inputId);
    }

    public function render(): View
    {
        return view('livewire.entity-individual-selector-modal');
    }
}
