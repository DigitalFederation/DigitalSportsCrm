<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Notifications\GenericCoachInvitationNotification;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\Models\EntityProfessionalRoleInvitation;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\Models\License;
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
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ManageEntityCoaches extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public Collection $professionalRoles;
    public ?Entity $entity = null;
    public array $entityFederationIds = [];
    public $sportsWithLicenses;

    public ?int $selectedSportId = null;

    // Property to hold the associated coaches
    // We use Computed property caching for efficiency
    #[Computed]
    public function associatedCoaches()
    {
        if (! $this->entity || $this->professionalRoles->isEmpty()) {
            return new EloquentCollection; // Return empty collection if no entity or roles
        }

        $query = EntityProfessionalRole::where('entity_id', $this->entity->id)
            ->whereIn('professional_role_id', $this->professionalRoles->keys()->toArray())
            // Only include Active or Pending coaches for exclusion
            // Canceled/Rejected coaches can be re-invited
            ->whereIn('status_class', [
                \Domain\Entities\States\ActiveEntityProfessionalRoleState::class,
                PendingEntityProfessionalRoleState::class,
            ]);

        // Only filter by sport if a sport is selected and the column exists
        if ($this->selectedSportId && \Schema::hasColumn('entity_professional_role', 'sport_id')) {
            $query->where('sport_id', $this->selectedSportId);
        }

        return $query
            // Eager load necessary relations
            ->with(['individual.country', 'professionalRole'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function mount(EloquentCollection $professionalRoles, $sportsWithLicenses): void
    {
        $this->professionalRoles = $professionalRoles->pluck('name', 'id');
        $this->sportsWithLicenses = $sportsWithLicenses;
        $this->entity = Auth::user()?->entities()->first();

        if (! $this->entity) {
            // Handle error: user not associated with an entity
            Log::warning('ManageEntityCoaches component loaded without a valid entity for user: ' . Auth::id());

            // Optionally redirect or show an error message
            return;
        }

        $this->entityFederationIds = $this->entity->federations()->pluck('federation.id')->toArray();

        if (empty($this->entityFederationIds)) {
            // Handle error: entity not associated with any federation
            Log::warning('ManageEntityCoaches component loaded without any valid federations for entity: ' . $this->entity->id);
            // Optionally redirect or show an error message
        }

        // Don't auto-select - let user choose explicitly to see invitation options
        // This makes the invitation functionality more discoverable
        $this->selectedSportId = null;
    }

    /**
     * Handle when the selected sport changes
     */
    public function updatedSelectedSportId(): void
    {
        // Force table to refresh when sport changes
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        if (! $this->entity || empty($this->entityFederationIds) || ! $this->selectedSportId) {
            // Return an empty table configuration if prerequisites are not met
            return $table->query(Individual::query()->whereRaw('1 = 0')); // Empty query
        }

        $entityId = $this->entity->id;
        $sportId = $this->selectedSportId;
        $relevantRoleIds = $this->professionalRoles->keys()->toArray();

        // Get IDs of individuals already associated with this entity for this sport
        $associatedIndividualIds = $this->associatedCoaches()->pluck('individual_id')->unique()->toArray();

        // Get coach licenses for the selected sport (only active ones)
        $coachLicenses = License::where(function ($q) use ($sportId) {
            $q->whereHas('sports', fn ($sq) => $sq->where('sports.id', $sportId))
                ->orWhere('sport_id', $sportId);
        })
            ->whereIn('professional_role_id', $relevantRoleIds)
            ->where('active', 1)
            ->pluck('id')
            ->toArray();

        // Get certification IDs linked to the sport via pivot (for generic certs without license_id)
        $certIdsViaPivot = Certification::whereHas('sports', fn ($q) => $q->where('sports.id', $sportId))
            ->whereIn('professional_role_id', $relevantRoleIds)
            ->pluck('id')
            ->toArray();

        // If no coach licenses AND no certifications via pivot exist, show empty table
        if (count($coachLicenses) === 0 && count($certIdsViaPivot) === 0) {
            return $table
                ->query(Individual::query()->whereRaw('1 = 0'))
                ->emptyStateHeading(__('coaches.no_licenses_for_sport'))
                ->emptyStateDescription(__('coaches.no_licenses_for_sport_desc'));
        }

        return $table
            ->query(
                Individual::query()
                    ->with([
                        'country',
                        'certificationsAttributed' => function ($query) {
                            $query->where('status_class', 'Domain\Certifications\States\ActiveCertificationAttributedState');
                        },
                    ])
                    // Must be an active member of THIS entity
                    ->whereHas('entities', function ($q) {
                        $q->where('entity.id', $this->entity->id)
                            ->where('individual_entity.status_class', ActiveIndividualEntityState::class);
                    })
                    // Must have active federation membership in entity's federations
                    ->whereHas('federations', function ($q) {
                        $q->whereIn('federation_id', $this->entityFederationIds)
                            ->where('status_class', ActiveIndividualFederationState::class);
                    })
                    // Exclude individuals already associated as coach in this entity for this sport
                    ->whereNotIn('id', $associatedIndividualIds)
                    // Must have an active license OR an active certification linked to the sport
                    ->where(function (Builder $q) use ($coachLicenses, $certIdsViaPivot) {
                        if (! empty($coachLicenses)) {
                            $q->whereHas('licenses', function (Builder $lq) use ($coachLicenses) {
                                $lq->where('status_class', ActiveLicenseAttributedState::class)
                                    ->whereIn('license_id', $coachLicenses);
                            });
                        }
                        if (! empty($certIdsViaPivot)) {
                            $q->orWhereHas('certificationsAttributed', function (Builder $cq) use ($certIdsViaPivot) {
                                $cq->where('status_class', ActiveCertificationAttributedState::class)
                                    ->whereIn('certification_id', $certIdsViaPivot);
                            });
                        }
                    })
            )
            ->columns([
                TextColumn::make('member_code')
                    ->label(__('main.Member Code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name') // Assuming an accessor exists, otherwise use name/surname
                    ->label(__('Name'))
                    ->searchable(['name', 'surname'])
                    ->sortable(['name']),
                ImageColumn::make('country.iso')
                    ->label(__('Country'))
                    ->getStateUsing(fn (Individual $record): string => asset('img/flags/' . strtolower($record->country?->iso ?? '') . '.svg'))
                    ->circular(),
                TextColumn::make('country.name')
                    ->label(__('Country Name')) // Hidden by default, useful for sorting/filtering
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filters can be added here if needed
            ])
            ->actions([
                Action::make('invite')
                    ->label(__('coaches.invite'))
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->extraAttributes(fn (Individual $record): array => [
                        'onclick' => "if(!confirm('" . addslashes(__('coaches.confirm_invite_coach', ['name' => $record->full_name])) . "')) { event.stopImmediatePropagation(); return false; }",
                    ])
                    ->visible(
                        fn (Individual $record): bool => ! EntityProfessionalRole::where('entity_id', $this->entity->id)
                            ->where('individual_id', $record->id)
                            ->whereIn('professional_role_id', $this->professionalRoles->keys()->toArray())
                            ->where('sport_id', $this->selectedSportId)
                            ->whereIn('status_class', [
                                ActiveEntityProfessionalRoleState::class,
                                PendingEntityProfessionalRoleState::class,
                            ])
                            ->exists()
                    )
                    ->action(function (Individual $record): void {
                        $this->inviteCoach($record);
                    }),
            ])
            ->bulkActions([
                // Bulk actions can be added here if needed
            ])
            ->emptyStateHeading(__('No coaches available'))
            ->emptyStateDescription(__('No coaches found with active licenses and certifications for the selected sport'))
            ->defaultSort('name', 'asc');
    }

    protected function inviteCoach(Individual $individual): void
    {
        if (! $this->entity || ! $this->selectedSportId) {
            Notification::make()
                ->title(__('Error'))
                ->body(__('Missing required data for invitation'))
                ->danger()
                ->send();

            return;
        }

        $userToNotify = $individual->user()->first();

        if (! $userToNotify) {
            Notification::make()
                ->title(__('Cannot Send Invitation'))
                ->body(__('This individual does not have an associated user account.'))
                ->danger()
                ->send();

            return;
        }

        try {
            DB::beginTransaction();

            $committeeCode = 'SPORT'; // Use SPORT for coaches
            $invitingEntityId = $this->entity->id;
            $invitedUserId = $userToNotify->id;

            // Get the first coach role for the invitation record
            $coachRoleId = $this->professionalRoles->keys()->first();

            // Attempt to create the pending invitation record
            // The unique constraint in the migration handles duplicates
            $invitation = EntityProfessionalRoleInvitation::create([
                'entity_id' => $invitingEntityId,
                'inviting_entity_id' => $invitingEntityId,
                'individual_id' => $individual->id,
                'invited_user_id' => $invitedUserId,
                'professional_role_id' => $coachRoleId,
                'sport_id' => $this->selectedSportId,
                'status_class' => PendingEntityProfessionalRoleState::class,
                'committee_code' => $committeeCode,
                'status' => 'pending',
                'message' => __('coaches.you_have_been_invited'),
                'expires_at' => Carbon::now()->addDays(7), // Match URL expiry
            ]);

            // Get certification IDs linked to the sport via pivot
            $certIdsViaPivot = Certification::whereHas('sports', fn ($q) => $q->where('sports.id', $this->selectedSportId))
                ->whereIn('professional_role_id', $this->professionalRoles->keys()->toArray())
                ->pluck('id')
                ->toArray();

            // Create EntityProfessionalRole records for each applicable coach role
            foreach ($this->professionalRoles->keys() as $roleId) {
                // Check if the individual has an active license for this role and sport
                $hasLicense = $individual->licenses()
                    ->where('status_class', ActiveLicenseAttributedState::class)
                    ->whereHas('license', function ($query) use ($roleId) {
                        $query->where('professional_role_id', $roleId)
                            ->where(function ($q) {
                                $q->whereHas('sports', fn ($sq) => $sq->where('sports.id', $this->selectedSportId))
                                    ->orWhere('sport_id', $this->selectedSportId);
                            });
                    })
                    ->exists();

                // Check if the individual has an active certification linked via pivot
                $hasCertification = ! empty($certIdsViaPivot) && $individual->certificationsAttributed()
                    ->where('status_class', ActiveCertificationAttributedState::class)
                    ->whereIn('certification_id', $certIdsViaPivot)
                    ->whereHas('certification', fn ($q) => $q->where('professional_role_id', $roleId))
                    ->exists();

                if ($hasLicense || $hasCertification) {
                    EntityProfessionalRole::create([
                        'entity_id' => $invitingEntityId,
                        'individual_id' => $individual->id,
                        'professional_role_id' => $roleId,
                        'sport_id' => $this->selectedSportId,
                        'status_class' => PendingEntityProfessionalRoleState::class,
                    ]);
                }
            }

            // Get sport name for notification
            $sportName = $this->sportsWithLicenses->find($this->selectedSportId)?->name ?? '';

            DB::commit();

            // Send notification outside transaction - don't fail invitation if email fails
            try {
                $userToNotify->notify(new GenericCoachInvitationNotification($this->entity, $committeeCode, $sportName));
            } catch (Exception $e) {
                Log::warning('Failed to send coach invitation notification: ' . $e->getMessage());
            }

            Notification::make()
                ->title(__('Invitation Sent Successfully'))
                ->body(__('An invitation has been sent to :name for :sport.', [
                    'name' => $individual->full_name,
                    'sport' => $sportName,
                ]))
                ->success()
                ->send();

            // Reset the computed property and table to refresh the list
            unset($this->associatedCoaches);
            $this->resetTable();

            // Dispatch event to close parent modal and refresh page
            $this->dispatch('coach-invited');

        } catch (UniqueConstraintViolationException $e) {
            DB::rollBack();
            // Handle the case where a pending invitation already exists
            Log::info('Attempted to send duplicate pending invitation.', [
                'entity' => $invitingEntityId,
                'user' => $invitedUserId,
                'committee' => $committeeCode,
                'sport' => $this->selectedSportId,
            ]);
            Notification::make()
                ->title(__('Invitation Already Pending'))
                ->body(__(':name already has a pending invitation for this sport.', ['name' => $individual->full_name]))
                ->warning()
                ->send();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating/sending generic coach invitation: ' . $e->getMessage(), [
                'entity_id' => $invitingEntityId,
                'individual_id' => $individual->id,
                'user_id' => $invitedUserId,
                'sport_id' => $this->selectedSportId,
                'exception' => $e, // Log full exception for debugging
            ]);
            Notification::make()
                ->title(__('Error Sending Invitation'))
                ->body('An unexpected error occurred while sending the invitation.')
                ->danger()
                ->send();
        }
    }

    // Method to handle removal of association
    public function removeAssociation(int|string $entityProfessionalRoleId): void
    {
        try {
            $entityProfessionalRole = EntityProfessionalRole::find($entityProfessionalRoleId);

            if (! $entityProfessionalRole) {
                Notification::make()
                    ->title(__('Association Not Found'))
                    ->body(__('The association may have already been removed.'))
                    ->warning()
                    ->send();

                // Refresh the list
                unset($this->associatedCoaches);

                return;
            }

            // Authorization check
            if (! $this->entity || $entityProfessionalRole->entity_id !== $this->entity->id) {
                Notification::make()
                    ->title(__('Unauthorized Action'))
                    ->danger()
                    ->send();

                return;
            }

            // Store necessary details before deleting the role
            $entityId = $entityProfessionalRole->entity_id;
            $userId = $entityProfessionalRole->individual?->user_id;
            $committeeCode = $entityProfessionalRole->professionalRole?->committee?->code;
            $memberCode = $entityProfessionalRole->individual?->member_code ?? 'N/A'; // Store for logging
            $sportId = $entityProfessionalRole->sport_id;

            // Log before deleting (optional, adjust properties as needed)
            activity()
                ->performedOn($entityProfessionalRole)
                ->withProperties([
                    'entity_id' => $entityProfessionalRole->entity_id,
                    'individual_id' => $entityProfessionalRole->individual_id,
                    'professional_role_id' => $entityProfessionalRole->professional_role_id,
                    'sport_id' => $sportId,
                    'member_code' => $memberCode,
                ])
                ->log("Coach relationship removed: {$memberCode}");

            // Delete the record
            $entityProfessionalRole->delete();

            // Clean up the corresponding generic invitation if it exists and user/committee known
            if ($userId && $committeeCode) {
                EntityProfessionalRoleInvitation::where('inviting_entity_id', $entityId)
                    ->where('invited_user_id', $userId)
                    ->where('committee_code', $committeeCode)
                    ->where('sport_id', $sportId)
                    ->delete();
                Log::info('Cleaned up related EntityProfessionalRoleInvitation after role deletion by entity via Livewire.', [
                    'entity_id' => $entityId,
                    'user_id' => $userId,
                    'committee_code' => $committeeCode,
                    'sport_id' => $sportId,
                ]);
            }

            // Send success notification
            Notification::make()
                ->title(__('Association Removed'))
                ->body(__('The coach association has been removed successfully.'))
                ->success()
                ->send();

            // Reset the computed property to refresh the list
            unset($this->associatedCoaches);
        } catch (Exception $exception) {
            Log::error('Error removing coach association: ' . $exception->getMessage());
            Notification::make()
                ->title(__('Error Removing Association'))
                ->body(__('An unexpected error occurred. Please try again.'))
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.manage-entity-coaches');
    }
}
