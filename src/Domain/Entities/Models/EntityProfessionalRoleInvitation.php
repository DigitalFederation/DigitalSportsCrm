<?php

declare(strict_types=1);

namespace Domain\Entities\Models;

use App\Models\Sport;
use App\Models\User;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Represents a pending professional role invitation for an entity.
 *
 * @property int $id
 * @property int $entity_id
 * @property int $individual_id
 * @property int $professional_role_id
 * @property string $status_class
 * @property string|null $accept_url
 * @property string|null $reject_url
 * @property string|null $message
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Entity $entity
 * @property-read Individual $individual
 * @property-read ProfessionalRole $professionalRole
 */
class EntityProfessionalRoleInvitation extends Model
{
    protected $table = 'entity_professional_role_invitations';

    protected $fillable = [
        // New structure
        'entity_id',
        'individual_id',
        'professional_role_id',
        'status_class',
        'message',
        // Old structure (for backward compatibility)
        'inviting_entity_id',
        'invited_user_id',
        'committee_code',
        'sport_id',
        'status',
        'expires_at',
    ];
    // Define casting for date fields
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the entity that sent the invitation.
     * Handles both new (entity_id) and old (inviting_entity_id) structures.
     */
    public function entity(): BelongsTo
    {
        // Check if new structure exists
        if ($this->entity_id) {
            return $this->belongsTo(Entity::class, 'entity_id');
        }

        // Fall back to old structure
        return $this->belongsTo(Entity::class, 'inviting_entity_id');
    }

    /**
     * Get the individual who was invited.
     * Only works with new structure.
     */
    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }

    /**
     * Get the professional role for this invitation.
     * Only works with new structure.
     */
    public function professionalRole(): BelongsTo
    {
        return $this->belongsTo(ProfessionalRole::class);
    }

    /**
     * Get the user who was invited (old structure).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }

    /**
     * Get the sport for this invitation.
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Send invitation notification.
     */
    public function sendInvitationNotification(): void
    {
        // Implementation for sending notification
        // This could be email, in-app notification, etc.
    }
}
