<?php

namespace Domain\Documents\Models;

use Database\Factories\DocumentTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $code
 * @property string|null $prefix
 */
class DocumentType extends Model
{
    use HasFactory;

    protected $table = 'document_type';

    protected $fillable = ['code', 'name', 'prefix'];

    protected static function newFactory(): DocumentTypeFactory
    {
        return DocumentTypeFactory::new();
    }

    public $timestamps = false;

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
