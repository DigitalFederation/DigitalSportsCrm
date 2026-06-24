<?php

declare(strict_types=1);

namespace Domain\Federations\Models;

use Database\Factories\FederationVotingRightFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $federation_id
 * @property int $year
 * @property string $general_assembly_status
 * @property string $technical_committee_status
 * @property string $scientific_committee_status
 * @property string $sport_committee_status
 * @property string $finswimming_commission_status
 * @property string $freediving_commission_status
 * @property string $aquathlon_commission_status
 * @property string $underwater_hockey_commission_status
 * @property string $underwater_rugby_commission_status
 * @property string $target_shooting_commission_status
 * @property string $sport_diving_commission_status
 * @property string $spearfishing_commission_status
 * @property string $orienteering_commission_status
 * @property string $visual_commission_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Federation $federation
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FederationVotingRight newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FederationVotingRight newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FederationVotingRight query()
 * @method static FederationVotingRightFactory factory($count = null, $state = [])
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class FederationVotingRight extends Model
{
    use HasFactory;

    protected static function newFactory(): FederationVotingRightFactory
    {
        return FederationVotingRightFactory::new();
    }

    protected $table = 'federation_voting_rights';

    public const STATUS_VOTING_RIGHT = 'Voting right';
    public const STATUS_SUSPENDED = 'Suspended';
    public const STATUS_PROBATION = 'Probation';
    public const STATUS_NO_VOTING_RIGHT = 'No Voting Right';

    public const STATUS_OPTIONS = [
        self::STATUS_VOTING_RIGHT,
        self::STATUS_SUSPENDED,
        self::STATUS_PROBATION,
        self::STATUS_NO_VOTING_RIGHT,
    ];

    protected $fillable = [
        'federation_id',
        'year',
        'general_assembly_status',
        'technical_committee_status',
        'scientific_committee_status',
        'sport_committee_status',
        'finswimming_commission_status',
        'freediving_commission_status',
        'aquathlon_commission_status',
        'underwater_hockey_commission_status',
        'underwater_rugby_commission_status',
        'target_shooting_commission_status',
        'sport_diving_commission_status',
        'spearfishing_commission_status',
        'orienteering_commission_status',
        'visual_commission_status',
        // federation_id is handled via relationship
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class);
    }
}
