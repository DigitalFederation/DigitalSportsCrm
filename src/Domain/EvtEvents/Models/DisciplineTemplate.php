<?php

namespace Domain\EvtEvents\Models;

use Database\Factories\DisciplineTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DisciplineTemplate extends Model
{
    use HasFactory;

    protected $table = 'evt_discipline_templates';

    protected $fillable = ['name', 'description'];

    protected static function newFactory(): DisciplineTemplateFactory
    {
        return DisciplineTemplateFactory::new();
    }

    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class, 'evt_template_discipline', 'template_id', 'discipline_id');
    }

}
