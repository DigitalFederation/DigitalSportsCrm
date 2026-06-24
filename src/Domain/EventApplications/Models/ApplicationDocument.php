<?php

namespace Domain\EventApplications\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string|null $file_name
 * @property string $file_path
 */
class ApplicationDocument extends Model
{
    use HasFactory;

    public const STORAGE_DISK = 'secure-media';

    protected $table = 'application_documents';

    protected static function newFactory(): \Database\Factories\Domain\EventApplications\ApplicationDocumentFactory
    {
        return \Database\Factories\Domain\EventApplications\ApplicationDocumentFactory::new();
    }

    protected $fillable = [
        'application_id',
        'template_id',
        'document_type',
        'uploaded_by_type',
        'uploaded_by_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(EventApplication::class, 'application_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ApplicationTemplate::class, 'template_id');
    }

    public function uploadedBy(): MorphTo
    {
        return $this->morphTo('uploaded_by', 'uploaded_by_type', 'uploaded_by_id');
    }
}
