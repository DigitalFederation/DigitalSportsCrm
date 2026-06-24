<?php

declare(strict_types=1);

namespace App\Livewire\Certifications\Wizard;

use Domain\Individuals\Actions\GetEligibleInstructorsQueryAction;
use Domain\Individuals\Models\Individual;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class DirectorSelectorTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $selectedSchoolId = null;
    public ?int $selectedFederationId = null;
    public ?string $selectedDirectorId = null;
    public ?string $committeeCode = null;

    public function mount(?int $selectedSchoolId, ?int $selectedFederationId, ?string $selectedDirectorId = null, ?string $committeeCode = null): void
    {
        $this->selectedSchoolId = $selectedSchoolId;
        $this->selectedFederationId = $selectedFederationId;
        $this->selectedDirectorId = $selectedDirectorId;
        $this->committeeCode = $committeeCode;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->getEligibleDirectorQuery())
            ->columns([
                TextColumn::make('name')
                    ->label(__('certifications.given_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('surname')
                    ->label(__('certifications.family_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('birthdate')
                    ->label(__('certifications.birthdate'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('member_number')
                    ->label(__('certifications.member_number'))
                    ->searchable(),
                TextColumn::make('member_code')
                    ->label(__('certifications.id_number'))
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('country')
                    ->label(__('certifications.country'))
                    ->relationship('country', 'name'),
            ])
            ->actions([
                Action::make('toggle_select')
                    ->label(fn (Individual $record): string => $record->id == $this->selectedDirectorId ? __('certifications.remove') : __('certifications.select_course_director'))
                    ->button()
                    ->size('md')
                    ->tooltip(fn (Individual $record): string => $record->id == $this->selectedDirectorId ? __('certifications.remove_this_director') : __('certifications.select_this_director'))
                    ->color(fn (Individual $record): string => $record->id == $this->selectedDirectorId ? 'danger' : 'primary')
                    ->action(function (Individual $record) {
                        if ($record->id == $this->selectedDirectorId) {
                            $this->dispatch('removeDirector')->to(\App\Livewire\Certifications\CertificationAttributionWizard::class);
                        } else {
                            $this->dispatch('selectDirector', individualId: $record->id)->to(\App\Livewire\Certifications\CertificationAttributionWizard::class);
                        }
                    })
                    ->requiresConfirmation(fn (Individual $record): bool => $record->id == $this->selectedDirectorId),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->searchable()
            ->recordUrl(null)
            ->striped()
            ->recordClasses(function (Individual $record): ?string {
                return $record->id == $this->selectedDirectorId ? 'bg-primary-100 dark:bg-gray-700 border-s-4 border-primary-500' : null;
            })
            // Add Custom Empty State
            ->emptyStateHeading(__('certifications.no_eligible_directors'))
            ->emptyStateDescription(__('certifications.ensure_instructors_associated'))
            ->emptyStateIcon('heroicon-o-users'); // Example icon
    }

    protected function getEligibleDirectorQuery(): Builder
    {
        $action = new GetEligibleInstructorsQueryAction;

        return $action(
            schoolId: $this->selectedSchoolId,
            federationId: $this->selectedFederationId,
            committeeCode: $this->committeeCode
        )->with([
            'country',
            'professionalRoleEntities.entity',
            'professionalRoleEntities.professionalRole',
        ])->orderBy('created_at', 'desc');
    }

    public function render(): View
    {
        return view('livewire.certifications.wizard.selector-table');
    }
}
