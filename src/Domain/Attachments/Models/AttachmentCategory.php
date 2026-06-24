<?php

namespace Domain\Attachments\Models;

use Database\Factories\AttachmentCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class AttachmentCategory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name'];

    protected static function boot()
    {
        parent::boot();

        // Clear cache when a new category is created
        static::updated(function ($category) {
            Cache::forget('attachment_categories');
        });
        static::created(function ($category) {
            Cache::forget('attachment_categories');
        });
        static::deleted(function ($category) {
            Cache::forget('attachment_categories');
        });
    }

    protected static function newFactory(): AttachmentCategoryFactory
    {
        return AttachmentCategoryFactory::new();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
