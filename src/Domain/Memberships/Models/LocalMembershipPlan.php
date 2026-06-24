<?php

namespace Domain\Memberships\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalMembershipPlan extends Model
{
    protected $table = 'local_membership_plan_associations';

    protected $fillable = [
        'local_federation_id',
        'membership_plan_id',
    ];

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }
}
