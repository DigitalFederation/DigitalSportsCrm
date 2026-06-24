<?php

namespace App\Models;

use Database\Factories\GroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static select(string ...$columns)
 * @method static insert(array[] $groups)
 */
class Group extends Model
{
    use HasFactory;

    protected $table = 'user_group';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
    ];

    protected static function newFactory(): GroupFactory
    {
        return GroupFactory::new();
    }

    /**
     * Get all the comments for the ClassGroup
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'group_id', 'id');
    }
}
