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

class EventIndividualSelectorModal extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $isOpen = false;
    public $inputId;
    public $tableLoaded = false;

    protected $listeners = ['open-event-modal' => 'openModal'];

    public function mount($inputId)
    {
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
        return 10;
    }

    protected function getTableQuery(): Builder
    {
        // For event role assignment, we want to show all individuals
        // regardless of federation affiliation since Admins manage events
        return Individual::query()->with(['country']);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('full_name')
                ->label('Full Name')
                ->searchable(['name', 'surname'])
                ->sortable(['name', 'surname'])
                ->getStateUsing(fn (Individual $record): string => $record->full_name),
            Tables\Columns\TextColumn::make('member_code')
                ->label('International Code')
                ->searchable(),
            Tables\Columns\TextColumn::make('country.name')
                ->label('Nationality')
                ->sortable(),
            Tables\Columns\TextColumn::make('birthdate')
                ->label('Birthdate')
                ->date(),
            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->searchable(),
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
                ->button()
                ->color('success'),
        ];
    }

    public function selectIndividual(Individual $individual): void
    {
        $this->isOpen = false;
        $this->dispatch('individual-selected',
            id: $individual->id,
            name: $individual->full_name,
            code: $individual->member_code,
            inputId: $this->inputId
        );
    }

    public function render(): View
    {
        return view('livewire.event-individual-selector-modal');
    }
}
