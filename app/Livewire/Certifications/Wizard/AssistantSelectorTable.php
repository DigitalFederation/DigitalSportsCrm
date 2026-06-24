<?php

declare(strict_types=1);

namespace App\Livewire\Certifications\Wizard;

use Domain\Entities\States\ActiveEntityProfessionalRoleState;
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

class AssistantSelectorTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $selectedSchoolId = null;
    public ?int $selectedFederationId = null;
    public ?string $selectedDirectorId = null;
    public array $selectedAssistantIds = []; // Passed in to show selection state
    public ?string $committeeCode = null;

    public function mount(?int $selectedSchoolId, ?int $selectedFederationId, ?string $selectedDirectorId, array $selectedAssistantIds, ?string $committeeCode = null): void
    {
        $this->selectedSchoolId = $selectedSchoolId;
        $this->selectedFederationId = $selectedFederationId;
        $this->selectedDirectorId = $selectedDirectorId;
        $this->selectedAssistantIds = $selectedAssistantIds;
        $this->committeeCode = $committeeCode;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->getEligibleAssistantQuery())
            ->columns([
                TextColumn::make('name')
                    ->label(__('certifications.given_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('surname')
                    ->label(__('certifications.family_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member_code')
                    ->label(__('certifications.member_code'))
                    ->searchable(),
                TextColumn::make('schools')
                    ->label(__('certifications.schools'))
                    ->getStateUsing(function (Individual $record): string {
                        if ($this->selectedSchoolId) {
                            $school = $record->professionalRoleEntities
                                ->firstWhere('entity_id', $this->selectedSchoolId)
                                ?->entity;

                            return $school?->name ?? __('certifications.n_a');
                        } elseif ($this->selectedFederationId) {
                            $relevantRoles = $record->professionalRoleEntities->filter(function ($roleEntity) {
                                return $roleEntity->status_class === ActiveEntityProfessionalRoleState::class
                                    && str_contains(strtoupper($roleEntity->professionalRole?->role ?? ''), 'INSTRUCTOR');
                            });
                            $schoolNames = $relevantRoles->pluck('entity.name')->filter()->unique()->implode(', ');

                            return $schoolNames ?: __('certifications.n_a');
                        }

                        return __('certifications.n_a');
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('professionalRoleEntities.entity', function (Builder $entityQuery) use ($search) {
                            $entityQuery->where('name', 'like', "%{$search}%");
                        });
                    }),
            ])
            ->filters([
                SelectFilter::make('country')
                    ->label(__('certifications.country'))
                    ->relationship('country', 'name'),
            ])
            ->actions([
                Action::make('toggle_select')
                    ->label(fn (Individual $record): string => in_array($record->id, $this->selectedAssistantIds, true) ? __('certifications.remove_assistant') : __('certifications.select_assistant'))
                    ->button()
                    ->size('md')
                    ->tooltip(fn (Individual $record): string => in_array($record->id, $this->selectedAssistantIds, true) ? __('certifications.remove_assistant_from_selection') : __('certifications.select_assistant'))
                    ->color(fn (Individual $record): string => in_array($record->id, $this->selectedAssistantIds, true) ? 'danger' : 'primary')
                    ->action(function (Individual $record) {
                        $this->dispatch('toggleAssistant', individualId: $record->id)->to(\App\Livewire\Certifications\CertificationAttributionWizard::class);
                    }),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->searchable()
            ->recordUrl(null)
            ->striped()
            ->recordClasses(function (Individual $record): ?string {
                return in_array($record->id, $this->selectedAssistantIds, true) ? 'bg-primary-100 dark:bg-gray-700 border-s-4 border-primary-500' : null;
            })
            ->emptyStateHeading(__('certifications.no_eligible_assistants'))
            ->emptyStateDescription(__('certifications.ensure_assistants_are_instructors'))
            ->emptyStateIcon('heroicon-o-user-group');
    }

    protected function getEligibleAssistantQuery(): Builder
    {
        $action = new GetEligibleInstructorsQueryAction;

        return $action(
            schoolId: $this->selectedSchoolId,
            federationId: $this->selectedFederationId,
            committeeCode: $this->committeeCode,
            excludeIndividualId: $this->selectedDirectorId
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
