<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Diving\States\ActiveDivingCertificationState;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Exception;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Livewire component for managing Diving Professionals (DIVINGPROFESSIONAL role).
 *
 * This is independent from ManageEntityInstructors which handles INSTRUCTOR/LEADER roles.
 * Diving Professionals require both an active DivingProfessionalCertification
 * AND an active license from DIVINGSERVICES or DIVING committee.
 */
class ManageEntityDivingProfessionals extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public Collection $professionalRoles;

    public ?Entity $entity = null;

    public array $entityFederationIds = [];

    public bool $showInviteSection = true;

    /**
     * Committee codes to check for active licenses.
     * DIVINGSERVICES is the primary committee for diving professionals.
     */
    protected array $validCommitteeCodes = ['DIVINGSERVICES', 'DIVING'];

    #[Computed]
    public function associatedProfessionals()
    {
        if (! $this->entity || $this->professionalRoles->isEmpty()) {
            return new EloquentCollection;
        }

        return EntityProfessionalRole::where('entity_id', $this->entity->id)
            ->whereIn('professional_role_id', $this->professionalRoles->keys()->toArray())
            ->with(['individual.country', 'professionalRole'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function mount(
        EloquentCollection $professionalRoles,
        bool $showInviteSection = true
    ): void {
        $this->professionalRoles = $professionalRoles->pluck('name', 'id');
        $this->showInviteSection = $showInviteSection;
        $this->entity = Auth::user()?->entities()->first();

        if (! $this->entity) {
            Log::warning('ManageEntityDivingProfessionals component loaded without a valid entity for user: ' . Auth::id());

            return;
        }

        $this->entityFederationIds = $this->entity->federations()->pluck('federation.id')->toArray();

        if (empty($this->entityFederationIds)) {
            Log::warning('ManageEntityDivingProfessionals component loaded without any valid federations for entity: ' . $this->entity->id);
        }
    }

    public function table(Table $table): Table
    {
        if (! $this->entity || empty($this->entityFederationIds)) {
            Log::warning('ManageEntityDivingProfessionals: Empty table - no entity or federations', [
                'has_entity' => (bool) $this->entity,
                'federation_ids' => $this->entityFederationIds,
            ]);

            return $table->query(Individual::query()->whereRaw('1 = 0'));
        }

        $relevantRoleIds = $this->professionalRoles->keys()->toArray();
        $associatedIndividualIds = $this->associatedProfessionals()
            ->filter(fn (EntityProfessionalRole $role) => in_array($role->status_class, [
                ActiveEntityProfessionalRoleState::class,
                PendingEntityProfessionalRoleState::class,
            ]))
            ->pluck('individual_id')
            ->unique()
            ->toArray();

        return $table
            ->query(
                Individual::query()
                    ->with(['country'])
                    // Must be in at least one of the entity's federations with active status
                    ->whereHas('federations', function ($q) {
                        $q->whereIn('federation_id', $this->entityFederationIds)
                            ->where('status_class', ActiveIndividualFederationState::class);
                    })
                    // Exclude individuals already associated for the relevant roles
                    ->whereNotIn('id', $associatedIndividualIds)
                    // Must have an active DivingProfessionalCertification
                    ->whereHas('divingProfessionalCertifications', function (Builder $certQuery) {
                        $certQuery->where('status_class', ActiveDivingCertificationState::class);
                    })
                    // AND an active license from DIVINGSERVICES/DIVING committee
                    ->whereHas('licenses', function (Builder $licenseQuery) {
                        $licenseQuery->withoutGlobalScope(ExcludeInternationalScope::class)
                            ->where('status_class', ActiveLicenseAttributedState::class)
                            ->whereHas('license', function (Builder $licenseSubQuery) {
                                $licenseSubQuery->whereHas('committee', fn ($q) => $q->whereIn('code', $this->validCommitteeCodes));
                            });
                    })
            )
            ->columns([
                TextColumn::make('member_number')
                    ->label(__('diving.member_number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label(__('common.name'))
                    ->searchable(['name', 'surname'])
                    ->sortable(['name']),
                ImageColumn::make('country.iso')
                    ->label(__('common.country'))
                    ->getStateUsing(fn (Individual $record): string => asset('img/flags/' . strtolower($record->country?->iso ?? '') . '.svg'))
                    ->circular(),
                TextColumn::make('country.name')
                    ->label(__('common.country_name'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Action::make('invite')
                    ->label(__('diving.invite'))
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->modalHeading(__('diving.invite_diving_professional'))
                    ->modalDescription(__('diving.invite_professional_confirmation_message'))
                    ->modalSubmitActionLabel(__('diving.send_invitation'))
                    ->modalCancelActionLabel(__('main.cancel'))
                    ->requiresConfirmation()
                    ->visible(
                        fn (Individual $record): bool => ! EntityProfessionalRole::where('entity_id', $this->entity->id)
                            ->where('individual_id', $record->id)
                            ->whereIn('professional_role_id', $this->professionalRoles->keys()->toArray())
                            ->whereIn('status_class', [
                                ActiveEntityProfessionalRoleState::class,
                                PendingEntityProfessionalRoleState::class,
                            ])
                            ->exists()
                    )
                    ->action(function (Individual $record): void {
                        $this->sendInvitation($record);
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('name', 'asc');
    }

    protected function sendInvitation(Individual $individual): void
    {
        try {
            // Get the first DIVINGPROFESSIONAL role
            $professionalRoleId = $this->professionalRoles->keys()->first();
            $professionalRole = ProfessionalRole::find($professionalRoleId);

            if (! $professionalRole) {
                Notification::make()
                    ->title(__('diving.error'))
                    ->body(__('diving.professional_role_not_found'))
                    ->danger()
                    ->send();

                return;
            }

            // Check if already exists (pending or active)
            $existingRole = EntityProfessionalRole::where('entity_id', $this->entity->id)
                ->where('individual_id', $individual->id)
                ->where('professional_role_id', $professionalRoleId)
                ->whereIn('status_class', [
                    PendingEntityProfessionalRoleState::class,
                    ActiveEntityProfessionalRoleState::class,
                ])
                ->exists();

            if ($existingRole) {
                Notification::make()
                    ->title(__('diving.invitation_already_exists'))
                    ->body(__('diving.invitation_already_sent_or_active', ['name' => $individual->full_name]))
                    ->warning()
                    ->send();

                return;
            }

            // Create pending EntityProfessionalRole directly (no signed URL flow)
            EntityProfessionalRole::create([
                'entity_id' => $this->entity->id,
                'individual_id' => $individual->id,
                'professional_role_id' => $professionalRoleId,
                'entity_name' => $this->entity->name,
                'individual_name' => $individual->full_name,
                'role_name' => $professionalRole->name,
                'status_class' => PendingEntityProfessionalRoleState::class,
            ]);

            Notification::make()
                ->title(__('diving.invitation_sent'))
                ->body(__('diving.invitation_sent_to', ['name' => $individual->full_name]))
                ->success()
                ->send();

            // Dispatch event to close modal and refresh
            $this->dispatch('diving-professional-invited');

            // Reset computed property to refresh the list
            unset($this->associatedProfessionals);

        } catch (Exception $e) {
            Log::error('Error sending diving professional invitation: ' . $e->getMessage(), [
                'entity_id' => $this->entity->id,
                'individual_id' => $individual->id,
                'exception' => $e,
            ]);

            Notification::make()
                ->title(__('diving.error_sending_invitation'))
                ->body(__('diving.error_occurred'))
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.manage-entity-diving-professionals');
    }
}
