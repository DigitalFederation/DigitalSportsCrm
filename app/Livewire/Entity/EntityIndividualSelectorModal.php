<?php

namespace App\Livewire\Entity;

use Domain\Individuals\Models\Individual;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class EntityIndividualSelectorModal extends Component implements HasForms, HasTable
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

    public function openModal()
    {
        $this->isOpen = true;
    }
    public function loadTable()
    {
        $this->tableLoaded = true;
    }
    protected function getTableQuery(): Builder
    {
        return Individual::query()
            ->whereHas('individualEntities', function ($query) {
                return $query->where('entity_id', $this->entityId)
                    ->where('status_class', 'Domain\Individuals\States\ActiveIndividualEntityState');
            });
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Full Name')
                ->searchable(['name', 'surname'])
                ->sortable(['name', 'surname'])
                ->getStateUsing(fn (Individual $record): string => $record->full_name),
            Tables\Columns\TextColumn::make('member_code')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('country.name')->label('Nationality')->sortable(),
            Tables\Columns\TextColumn::make('birthdate')->label('Birthdate')->sortable(),
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
        $this->dispatch('entity-individual-selected',
            code : $individual->member_code,
            inputId : $this->inputId,
        );
    }

    public function render(): View
    {
        return view('livewire.entity.entity-individual-selector-modal');
    }
}
