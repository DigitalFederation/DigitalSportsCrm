<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Notifications\GenericInstructorInvitationNotification;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\Models\EntityProfessionalRoleInvitation;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualEntityState;
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
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ManageEntityInstructors extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public Collection $professionalRoles;

    public ?Entity $entity = null;

    public array $entityFederationIds = [];

    public bool $showAssociatedSection = true;

    public bool $showInviteSection = false;

    public bool $requiresInternational = false;

    public string $committeeCode = 'DIVING';

    // Property to hold the associated instructors
    // We use Computed property caching for efficiency
    #[Computed]
    public function associatedInstructors()
    {
        if (! $this->entity || $this->professionalRoles->isEmpty()) {
            return new EloquentCollection; // Return empty collection if no entity or roles
        }

        return EntityProfessionalRole::where('entity_id', $this->entity->id)
            ->whereIn('professional_role_id', $this->professionalRoles->keys()->toArray())
            // Eager load necessary relations
            ->with(['individual.country', 'professionalRole'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function mount(
        EloquentCollection $professionalRoles,
        bool $showAssociatedSection = true,
        bool $showInviteSection = false,
        bool $requiresInternational = false
    ): void {
        $this->professionalRoles = $professionalRoles->pluck('name', 'id');
        $firstRoleId = $professionalRoles->first()?->getKey();
        if ($firstRoleId) {
            $this->committeeCode = ProfessionalRole::with('committee')
                ->find($firstRoleId)
                ?->committee
                ?->code ?? $this->committeeCode;
        }
        $this->showAssociatedSection = $showAssociatedSection;
        $this->showInviteSection = $showInviteSection;
        $this->requiresInternational = $requiresInternational;
        $this->entity = Auth::user()?->entities()->first();

        if (! $this->entity) {
            // Handle error: user not associated with an entity
            Log::warning('ManageEntityInstructors component loaded without a valid entity for user: ' . Auth::id());

            // Optionally redirect or show an error message
            return;
        }

        $this->entityFederationIds = $this->entity->federations()->pluck('federation.id')->toArray();

        if (empty($this->entityFederationIds)) {
            // Handle error: entity not associated with any federation
            Log::warning('ManageEntityInstructors component loaded without any valid federations for entity: ' . $this->entity->id);
            // Optionally redirect or show an error message
        }
    }

    public function table(Table $table): Table
    {
        if (! $this->entity || empty($this->entityFederationIds)) {
            // Return an empty table configuration if prerequisites are not met
            Log::warning('ManageEntityInstructors: Empty table - no entity or federations', [
                'has_entity' => (bool) $this->entity,
                'federation_ids' => $this->entityFederationIds,
            ]);

            return $table->query(Individual::query()->whereRaw('1 = 0')); // Empty query
        }

        $entityId = $this->entity->id;
        $relevantRoleIds = $this->professionalRoles->keys()->toArray();
        // Get IDs of individuals already associated
        $associatedIndividualIds = $this->associatedInstructors()->pluck('individual_id')->unique()->toArray();

        return $table
            ->query(
                Individual::query()
                    ->with([
                        'country',
                        // No need to eager load professionalRoleEntities here anymore for the main query
                        // 'professionalRoleEntities' => fn($query) =>
                        // $query->where('entity_id', $entityId)->whereIn('professional_role_id', $relevantRoleIds)
                    ])
                    // Only show individuals who are active members of this entity
                    ->whereHas('entities', function ($q) {
                        $q->where('entity.id', $this->entity->id)
                            ->where('individual_entity.status_class', ActiveIndividualEntityState::class);
                    })
                    // Ensure they are in at least one of the entity's federations with active status
                    ->whereHas('federations', function ($q) {
                        $q->whereIn('federation_id', $this->entityFederationIds)
                            ->where('status_class', ActiveIndividualFederationState::class);
                    })

                    // KEEP: Exclude individuals already associated in this entity for the relevant roles
                    ->whereNotIn('id', $associatedIndividualIds)
                    // KEEP: Ensure they have an active license for a role in the same committee as the instructor roles
                    // Note: We bypass ExcludeInternationalScope to include international licenses (e.g., international instructor licenses)
                    ->whereHas('licenses', function (Builder $licenseQuery) use ($relevantRoleIds) {
                        $licenseQuery->withoutGlobalScope(ExcludeInternationalScope::class)
                            ->where('status_class', ActiveLicenseAttributedState::class)
                            ->whereHas('license', function (Builder $licenseSubQuery) use ($relevantRoleIds) {
                                // Get committee IDs from the relevant instructor roles
                                $committeeIds = ProfessionalRole::whereIn('id', $relevantRoleIds)->pluck('committee_id');
                                $licenseSubQuery->whereHas('professionalRole', fn ($q) => $q->whereIn('committee_id', $committeeIds));

                                // If requiresInternational is true, only show individuals with international licenses
                                if ($this->requiresInternational) {
                                    $licenseSubQuery->whereHas('committee', fn ($q) => $q->where('is_international', true));
                                }
                            });
                    })
                    // ADD: Ensure they have at least one active certification for INSTRUCTOR/LEADER professional roles
                    ->whereHas('certificationsAttributed', function (Builder $certQuery) use ($relevantRoleIds) {
                        $certQuery->where('status_class', 'Domain\Certifications\States\ActiveCertificationAttributedState')
                            ->whereHas('certification', function (Builder $certificationQuery) use ($relevantRoleIds) {
                                $certificationQuery->whereIn('professional_role_id', $relevantRoleIds);
                            });
                    })
                // Additional condition: Only show individuals with an active license? Or active certification?
                // ->whereHas('licenses', fn (Builder $query) => $query->where('status_class', ActiveLicenseAttributedState::class))
                // ->whereHas('certificationsAttributed', fn (Builder $query) => $query->where('status_class', ActiveCertificationAttributedState::class))
            )
            ->columns([
                TextColumn::make('member_code')
                    ->label(__('International Code'))
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
                // Remove this column as it's redundant for non-associated individuals and causes lazy loading issues
                /* ViewColumn::make('association_status')
                    ->label(__('Association Status'))
                    ->view('tables.columns.entity-professional-role-status') */
            ])
            ->filters([
                // Filters might need rethinking now, as the table only shows non-associated users.
                // Keeping them might still be useful if further filtering is desired on the non-associated list.
            /* Filter::make('associated') Removed as table only shows non-associated
                    ->label('Already Associated')
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->whereHas(
                            'professionalRoleEntities',
                            fn(Builder $q) =>
                            $q->where('entity_id', $entityId)->whereIn('professional_role_id', $relevantRoleIds)
                        )
                    ),
                 Filter::make('not_associated') Removed as table only shows non-associated
                    ->label('Not Associated')
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->whereDoesntHave(
                            'professionalRoleEntities',
                            fn(Builder $q) =>
                            $q->where('entity_id', $entityId)->whereIn('professional_role_id', $relevantRoleIds)
                        )
                    ), */])
            ->actions([
                Action::make('invite')
                    ->label(__('international_diving.invite'))
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->modalHeading(__('international_diving.invite_instructor_or_leader'))
                    ->modalDescription(__('international_diving.invite_confirmation_message'))
                    ->modalSubmitActionLabel(__('international_diving.send_invitation'))
                    ->modalCancelActionLabel(__('main.cancel'))
                    ->requiresConfirmation()
                    // ->visible() check remains useful to prevent inviting already fully associated people (though the main query handles this)
                    ->visible(
                        fn (Individual $record): bool =>
                        // This check might need refinement depending on how generic pending invites are tracked.
                        // For now, it prevents inviting someone already associated with *any* relevant role.
                        ! EntityProfessionalRole::where('entity_id', $this->entity->id)
                            ->where('individual_id', $record->id)
                            ->whereIn('professional_role_id', $this->professionalRoles->keys()->toArray())
                            // Optional: Add check for Active/Pending status if needed
                            ->exists()
                    )
                    // Remove the form section entirely
                    // ->form([...])
                    ->action(function (Individual $record): void {
                        $userToNotify = $record->user()->first();

                        if (! $userToNotify) {
                            Notification::make()
                                ->title(__('Cannot Send Invitation'))
                                ->body(__('This individual does not have an associated user account.'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $committeeCode = $this->committeeCode;
                        $invitingEntityId = $this->entity->id;
                        $invitedUserId = $userToNotify->id;

                        try {
                            // Get the first instructor role for the invitation record
                            $instructorRoleId = $this->professionalRoles->keys()->first();

                            // Attempt to create the pending invitation record
                            // The unique constraint in the migration handles duplicates
                            $invitation = EntityProfessionalRoleInvitation::create([
                                'entity_id' => $invitingEntityId,
                                'inviting_entity_id' => $invitingEntityId,
                                'individual_id' => $record->id,
                                'invited_user_id' => $invitedUserId,
                                'professional_role_id' => $instructorRoleId,
                                'status_class' => PendingEntityProfessionalRoleState::class,
                                'committee_code' => $committeeCode,
                                'status' => 'pending',
                                'message' => __('international_diving.you_have_been_invited'),
                                'expires_at' => Carbon::now()->addDays(7),
                            ]);

                            // Dispatch the notification (don't let mail failures block the invitation)
                            try {
                                $userToNotify->notify(new GenericInstructorInvitationNotification($this->entity, $committeeCode));
                            } catch (Exception $e) {
                                Log::warning('Failed to send instructor invitation notification email.', [
                                    'entity_id' => $invitingEntityId,
                                    'individual_id' => $record->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }

                            Notification::make()
                                ->title(__('Invitation Sent Successfully'))
                                ->body(__('An invitation has been sent to :name.', ['name' => $record->full_name]))
                                ->success()
                                ->send();

                            // Dispatch event to close modal and refresh page
                            $this->dispatch('instructor-invited');
                        } catch (UniqueConstraintViolationException $e) {
                            // Handle the case where a pending invitation already exists
                            Log::info('Attempted to send duplicate pending invitation.', ['entity' => $invitingEntityId, 'user' => $invitedUserId, 'committee' => $committeeCode]);
                            Notification::make()
                                ->title(__('Invitation Already Pending'))
                                ->body(__(':name already has a pending invitation for this context.', ['name' => $record->full_name]))
                                ->warning()
                                ->send();
                        } catch (Exception $e) {
                            Log::error('Error creating/sending generic instructor invitation: ' . $e->getMessage(), [
                                'entity_id' => $invitingEntityId,
                                'individual_id' => $record->id,
                                'user_id' => $invitedUserId,
                                'exception' => $e, // Log full exception for debugging
                            ]);
                            Notification::make()
                                ->title(__('Error Sending Invitation'))
                                ->body('An unexpected error occurred while sending the invitation.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                // Bulk actions
            ])
            ->defaultSort('name', 'asc');
    }

    // Method to handle removal of association
    public function removeAssociation(int|string $entityProfessionalRoleId): void
    {
        try {
            $entityProfessionalRole = EntityProfessionalRole::findOrFail($entityProfessionalRoleId);

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

            // Log before deleting (optional, adjust properties as needed)
            activity()
                ->performedOn($entityProfessionalRole)
                ->withProperties([
                    'entity_id' => $entityProfessionalRole->entity_id,
                    'individual_id' => $entityProfessionalRole->individual_id,
                    'professional_role_id' => $entityProfessionalRole->professional_role_id,
                    'member_code' => $memberCode,
                ])
                ->log("Instructor/Leader relationship removed: {$memberCode}");

            // Delete the record
            $entityProfessionalRole->delete();

            // Clean up the corresponding generic invitation if it exists and user/committee known
            if ($userId && $committeeCode) {
                EntityProfessionalRoleInvitation::where('inviting_entity_id', $entityId)
                    ->where('invited_user_id', $userId)
                    ->where('committee_code', $committeeCode)
                    ->delete();
                Log::info('Cleaned up related EntityProfessionalRoleInvitation after role deletion by entity via Livewire.', [
                    'entity_id' => $entityId,
                    'user_id' => $userId,
                    'committee_code' => $committeeCode,
                ]);
            }

            // Send success notification
            Notification::make()
                ->title(__('Association Removed'))
                ->body(__('The instructor/leader association has been removed successfully.'))
                ->success()
                ->send();

            // Reset the computed property to refresh the list
            unset($this->associatedInstructors);
        } catch (Exception $exception) {
            Log::error('Error removing instructor association: ' . $exception->getMessage());
            Notification::make()
                ->title(__('Error Removing Association'))
                ->body(__('An unexpected error occurred. Please try again.')) // Provide a generic message
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.manage-entity-instructors');
    }
}
