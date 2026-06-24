<?php

namespace Domain\EventApplications\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $application_id
 */
class ApplicationComment extends Model
{
    use HasFactory;

    protected $table = 'application_comments';

    protected $fillable = [
        'application_id',
        'user_id',
        'comment',
        'section',
        'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(EventApplication::class, 'application_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
