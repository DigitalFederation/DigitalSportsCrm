<?php

namespace Domain\EvtEvents\Models;

use Database\Factories\EvtAttributeRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeRules extends Model
{
    use HasFactory;

    protected $table = 'evt_attribute_rules';

    protected $fillable = [
        'name',
        'attribute_id',
        'operator',
        'default_value',
        'comparison_field',
        'comparison_value',
        'is_validation',
        'max_value',
        'min_value',
    ];

    protected static function newFactory(): EvtAttributeRuleFactory
    {
        return EvtAttributeRuleFactory::new();
    }

    /**
     * Method to check if the rule is valid based on current values
     */
    public function isValid($value, $context = [])
    {
        if (! $this->is_validation) {
            return true;
        }

        switch ($this->operator) {
            case 'max':
                return $value <= $this->comparison_value;
            case 'regex':
                return preg_match($this->comparison_value, $value);
            case 'unique':
                return ! in_array($value, $context);
            default:
                return true;
        }
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
