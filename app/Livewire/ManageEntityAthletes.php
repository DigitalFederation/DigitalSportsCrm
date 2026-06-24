<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityAthlete;
use Domain\Entities\Models\EntityProfessionalRoleInvitation;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ManageEntityAthletes extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?Entity $entity = null;
    public array $entityFederationIds = [];
    public ?ProfessionalRole $athleteRole = null;

    public ?int $selectedSportId = null;

    public $sportsWithLicenses;

    public function mount($sportsWithLicenses): void
    {
        $this->entity = Auth::user()?->entities()->first();
        $this->sportsWithLicenses = $sportsWithLicenses;
        $this->athleteRole = ProfessionalRole::where('role', 'ATHLETE')->first();

        if (! $this->entity) {
            Log::warning('ManageEntityAthletes component loaded without a valid entity for user: ' . Auth::id());

            return;
        }

        if (! $this->athleteRole) {
            Log::warning('ManageEntityAthletes component loaded without ATHLETE professional role in system');
        }

        $this->entityFederationIds = $this->entity->federations()->pluck('federation.id')->toArray();

        if (empty($this->entityFederationIds)) {
            Log::warning('ManageEntityAthletes component loaded without any valid federations for entity: ' . $this->entity->id);
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
        $this->resetTable();
    }

    private function canShowTable(): bool
    {
        return $this->entity
            && ! empty($this->entityFederationIds)
            && $this->selectedSportId
            && $this->athleteRole;
    }

    private function emptyTable(Table $table): Table
    {
        return $table->query(Individual::query()->whereRaw('1 = 0'));
    }

    private function getAthleteLicenses(): array
    {
        return License::where('sport_id', $this->selectedSportId)
            ->where('professional_role_id', $this->athleteRole->id)
            ->where('active', 1)
            ->pluck('id')
            ->toArray();
    }

    private function buildQuery(array $athleteLicenses): Builder
    {
        // Get exclusion lists
        $excludedAthletes = $this->getExcludedAthletes();
        $excludedInvitations = $this->getExcludedInvitations();

        return Individual::query()
            ->with(['country'])
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
            ->whereNotIn('id', $excludedAthletes)
            ->whereNotIn('id', $excludedInvitations)
            // Must have an active athlete license for this sport
            ->whereHas('licenses', function ($q) use ($athleteLicenses) {
                $q->where('status_class', ActiveLicenseAttributedState::class)
                    ->whereIn('license_id', $athleteLicenses);
            });
    }

    private function getExcludedAthletes(): array
    {
        // Exclude athletes who are already active OR pending with ANY entity for this sport
        // An athlete can only be associated with ONE entity at a time per sport
        // Canceled/Rejected athletes can be re-invited by any entity
        return EntityAthlete::where('sport_id', $this->selectedSportId)
            ->whereIn('status_class', [
                ActiveEntityProfessionalRoleState::class,
                PendingEntityProfessionalRoleState::class,
            ])
            ->pluck('individual_id')
            ->toArray();
    }

    private function getExcludedInvitations(): array
    {
        // Also exclude athletes with pending invitations (in case EntityAthlete doesn't exist yet)
        return EntityProfessionalRoleInvitation::where('entity_id', $this->entity->id)
            ->where('professional_role_id', $this->athleteRole->id)
            ->where('status_class', PendingEntityProfessionalRoleState::class)
            ->pluck('individual_id')
            ->toArray();
    }

    public function table(Table $table): Table
    {
        // Return empty table if prerequisites not met
        if (! $this->canShowTable()) {
            return $this->emptyTable($table);
        }

        // Get athlete licenses for the sport
        $athleteLicenses = $this->getAthleteLicenses();

        // No licenses = no athletes to show
        if (empty($athleteLicenses)) {
            return $this->emptyTable($table);
        }

        return $table
            ->query($this->buildQuery($athleteLicenses))
            ->columns([
                TextColumn::make('member_code')
                    ->label(__('main.Member Code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label(__('Name'))
                    ->searchable(['name', 'surname'])
                    ->sortable(['name']),
                ImageColumn::make('country.iso')
                    ->label(__('Country'))
                    ->getStateUsing(
                        fn (Individual $record): string => asset('img/flags/' . strtolower($record->country?->iso ?? '') . '.svg')
                    )
                    ->circular(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([])
            ->actions([
                Action::make('invite')
                    ->label(__('athletes.invite'))
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->extraAttributes(fn (Individual $record): array => [
                        'onclick' => "if(!confirm('" . addslashes(__('athletes.confirm_invite_athlete', ['name' => $record->full_name])) . "')) { event.stopImmediatePropagation(); return false; }",
                    ])
                    ->action(function (Individual $individual) {
                        $this->inviteAthlete($individual);
                    }),
            ])
            ->bulkActions([])
            ->emptyStateHeading(__('athletes.no_athletes_available'))
            ->emptyStateDescription(__('athletes.no_athletes_available_desc'));
    }

    public function inviteAthlete(Individual $individual): void
    {
        if (! $this->canShowTable()) {
            $this->sendErrorNotification(__('athletes.missing_required_data'));

            return;
        }

        try {
            DB::beginTransaction();

            if ($this->isAlreadyAssociated($individual)) {
                $this->sendWarningNotification(__('athletes.already_associated'));

                return;
            }

            if ($this->hasPendingInvitation($individual)) {
                $this->sendWarningNotification(__('athletes.invitation_already_sent'));

                return;
            }

            $invitation = $this->createInvitation($individual);
            $this->createPendingAthlete($individual);

            DB::commit();

            // Send notification outside transaction - don't fail invitation if email fails
            try {
                $this->sendInvitationNotification($individual, $invitation);
            } catch (Exception $e) {
                Log::warning('Failed to send athlete invitation notification: ' . $e->getMessage());
            }

            $this->sendSuccessNotification();
            $this->logInvitation($individual, $invitation);
            $this->resetTable();

            // Dispatch event to close parent modal and refresh page
            $this->dispatch('athlete-invited');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to send athlete invitation: ' . $e->getMessage());
            $this->sendErrorNotification(__('athletes.invitation_failed'));
        }
    }

    private function isAlreadyAssociated(Individual $individual): bool
    {
        return EntityAthlete::where('individual_id', $individual->id)
            ->where('sport_id', $this->selectedSportId)
            ->where('status_class', ActiveEntityProfessionalRoleState::class)
            ->exists();
    }

    private function hasPendingInvitation(Individual $individual): bool
    {
        return EntityProfessionalRoleInvitation::where('entity_id', $this->entity->id)
            ->where('individual_id', $individual->id)
            ->where('professional_role_id', $this->athleteRole->id)
            ->where('status_class', PendingEntityProfessionalRoleState::class)
            ->exists();
    }

    private function createInvitation(Individual $individual): EntityProfessionalRoleInvitation
    {
        return EntityProfessionalRoleInvitation::create([
            'entity_id' => $this->entity->id,
            'inviting_entity_id' => $this->entity->id,
            'individual_id' => $individual->id,
            'invited_user_id' => $individual->user_id ?? null,
            'professional_role_id' => $this->athleteRole->id,
            'status_class' => PendingEntityProfessionalRoleState::class,
            'status' => 'pending',
            'message' => __('athletes.you_have_been_invited'),
            'expires_at' => \Carbon\Carbon::now()->addDays(7),
        ]);
    }

    private function createPendingAthlete(Individual $individual): void
    {
        // Use updateOrCreate to handle re-invitations (e.g., after rejection/cancellation)
        EntityAthlete::updateOrCreate(
            [
                'entity_id' => $this->entity->id,
                'individual_id' => $individual->id,
                'sport_id' => $this->selectedSportId,
            ],
            [
                'entity_name' => $this->entity->name,
                'individual_name' => $individual->full_name,
                'sport_name' => $this->sportsWithLicenses->find($this->selectedSportId)?->name,
                'status_class' => PendingEntityProfessionalRoleState::class,
            ]
        );
    }

    private function sendInvitationNotification(Individual $individual, EntityProfessionalRoleInvitation $invitation): void
    {
        if ($individual->user) {
            $sportName = $this->sportsWithLicenses->find($this->selectedSportId)?->name;
            $individual->user->notify(new \App\Notifications\GenericCoachInvitationNotification(
                $this->entity,
                'athlete',
                $sportName
            ));
        }
    }

    private function sendErrorNotification(string $message): void
    {
        Notification::make()
            ->title(__('athletes.error'))
            ->body($message)
            ->danger()
            ->send();
    }

    private function sendWarningNotification(string $message): void
    {
        DB::rollBack();
        Notification::make()
            ->title(__('athletes.cannot_invite'))
            ->body($message)
            ->warning()
            ->send();
    }

    private function sendSuccessNotification(): void
    {
        Notification::make()
            ->title(__('athletes.invitation_sent_success'))
            ->body(__('athletes.invitation_sent_success_desc'))
            ->success()
            ->send();
    }

    private function logInvitation(Individual $individual, EntityProfessionalRoleInvitation $invitation): void
    {
        activity()
            ->performedOn($invitation)
            ->withProperties([
                'entity_id' => $this->entity->id,
                'individual_id' => $individual->id,
                'selected_sport_id' => $this->selectedSportId,
            ])
            ->log(__('athletes.activity_invitation_sent', ['member_code' => $individual->member_code]));
    }

    public function render()
    {
        return view('livewire.manage-entity-athletes');
    }
}
