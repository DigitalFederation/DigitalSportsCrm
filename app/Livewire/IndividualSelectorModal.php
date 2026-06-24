<?php

namespace App\Livewire;

use Domain\Individuals\Models\Individual;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class IndividualSelectorModal extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $isOpen = false;
    public $federationId;
    public $inputId;
    public $tableLoaded = false;

    protected $listeners = ['open-modal' => 'openModal'];

    public function mount($federationId, $inputId)
    {
        $this->federationId = $federationId;
        $this->inputId = $inputId;
    }

    public function loadTable()
    {
        $this->tableLoaded = true;
    }
    public function openModal()
    {
        $this->isOpen = true;
    }

    public function getTableRecordsPerPage(): int
    {
        return 5;
    }

    protected function getTableQuery(): Builder
    {

        $query = Individual::query();

        if ($this->federationId !== null) {
            $query->whereHas('individualFederations', function ($subQuery) {
                return $subQuery->where('federation_id', $this->federationId)
                    ->where('status_class', 'Domain\Individuals\States\ActiveIndividualFederationState');
            });
        } elseif (auth()->user()->isFederation()) {
            // If no specific federation is set but the user is a federation user,
            // still filter by their federation
            $federationId = auth()->user()->federations()->first()->id;
            $query->whereHas('individualFederations', function ($subQuery) use ($federationId) {
                return $subQuery->where('federation_id', $federationId)
                    ->where('status_class', 'Domain\Individuals\States\ActiveIndividualFederationState');
            });
        }

        // If $federationId is null and user is not a federation user (i.e., admin user),
        // no additional filtering is applied, showing all individuals

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
            Tables\Columns\TextColumn::make('member_code')->searchable(),
            Tables\Columns\TextColumn::make('country.name')->label('Nationality')->sortable(),
            Tables\Columns\TextColumn::make('birthdate')->label('Birthdate'),
            Tables\Columns\TextColumn::make('individualFederations.national_federation_number')
                ->label('National Federation Nº')
                ->searchable()
                ->getStateUsing(function (Individual $record) {
                    return $record->individualFederations->where('federation_id', $this->federationId)->first()->national_federation_number ?? null;
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
        return view('livewire.individual-selector-modal');
    }
}
