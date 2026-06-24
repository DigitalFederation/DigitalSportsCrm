<?php

declare(strict_types=1);

namespace App\Livewire\Certifications\Wizard;

use Domain\Individuals\Models\Individual;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class StudentSelectorTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $selectedSchoolId = null;
    public ?int $selectedFederationId = null;
    public array $selectedStudentIds = []; // Passed in to show selection state

    public function mount(?int $selectedSchoolId, ?int $selectedFederationId, array $selectedStudentIds): void
    {
        $this->selectedSchoolId = $selectedSchoolId;
        $this->selectedFederationId = $selectedFederationId;
        $this->selectedStudentIds = $selectedStudentIds;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->getEligibleStudentQuery())
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular(),

                TextColumn::make('name')
                    ->label(__('certifications.given_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('surname')
                    ->label(__('certifications.family_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member_number')
                    ->label(__('certifications.member_number'))
                    ->searchable(),
                TextColumn::make('birthdate')
                    ->label(__('certifications.birthdate'))
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('toggle_select')
                    ->label(fn (Individual $record): string => in_array($record->id, $this->selectedStudentIds, true) ? __('certifications.remove_student') : __('certifications.select_student'))
                    ->button()
                    ->size('md')
                    ->tooltip(fn (Individual $record) => in_array($record->id, $this->selectedStudentIds, true) ? __('certifications.remove_student_from_selection') : __('certifications.select_student'))
                    ->color(fn (Individual $record): string => in_array($record->id, $this->selectedStudentIds, true) ? 'danger' : 'primary')
                    ->action(function (Individual $record) {
                        $this->dispatch('toggleStudent', individualId: $record->id)->to(\App\Livewire\Certifications\CertificationAttributionWizard::class);
                    }),
            ])
            ->filters([
                SelectFilter::make('country')
                    ->label(__('certifications.country'))
                    ->relationship('country', 'name'),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->searchable()
            ->recordUrl(null)
            ->striped()
            ->recordClasses(function (Individual $record): ?string {
                return in_array($record->id, $this->selectedStudentIds, true) ? 'bg-primary-100 dark:bg-gray-700 border-s-4 border-primary-500' : null;
            })
            ->emptyStateHeading(__('certifications.no_individuals_found'))
            ->emptyStateDescription(__('certifications.check_registration'))
            ->emptyStateIcon('heroicon-o-academic-cap');
    }

    protected function getEligibleStudentQuery(): Builder
    {
        return Individual::query()
            ->with(['country'])
            ->when(
                $this->selectedSchoolId,
                fn ($q) => $q->whereHas('individualEntities', fn ($ie) => $ie->where('entity_id', $this->selectedSchoolId))
            )
            ->when(
                $this->selectedFederationId && ! $this->selectedSchoolId,
                fn ($q) => $q->whereHas('federations', fn ($sq) => $sq->where('federation.id', $this->selectedFederationId))
            )
            ->orderBy('created_at', 'desc');
    }

    public function render(): View
    {
        return view('livewire.certifications.wizard.selector-table');
    }
}
