<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAttributeRuleOperatorsEnum;
use Illuminate\Support\Facades\DB;

class ProcessAttributeRulesAction
{
    public function execute(
        $value,
        string $operator,
        $table_column,
        $filter_column = null,
        $filter_value = null,
        $comparison_value = null,
        bool $is_validation = false
    ) {
        if ($is_validation) {
            return $this->validate($value, $operator, $comparison_value);
        }

        return $this->modify($value, $operator, $table_column, $filter_column, $filter_value, $comparison_value);
    }

    public function validate(
        $value,
        string $operator,
        $comparison_value
    ): bool {
        return match ($operator) {
            EvtAttributeRuleOperatorsEnum::EQUAL->name => $value == $comparison_value,
            EvtAttributeRuleOperatorsEnum::NOT_EQUAL->name => $value != $comparison_value,
            EvtAttributeRuleOperatorsEnum::IDENTICAL->name => $value === $comparison_value,
            EvtAttributeRuleOperatorsEnum::NOT_IDENTICAL->name => $value !== $comparison_value,
            EvtAttributeRuleOperatorsEnum::GREATER_THAN->name => $value > $comparison_value,
            EvtAttributeRuleOperatorsEnum::LESS_THAN->name => $value < $comparison_value,
            EvtAttributeRuleOperatorsEnum::GREATER_THAN_OR_EQUAL->name => $value >= $comparison_value,
            EvtAttributeRuleOperatorsEnum::LESS_THAN_OR_EQUAL->name => $value <= $comparison_value,
            EvtAttributeRuleOperatorsEnum::REGEX_MATCH->name => preg_match($comparison_value, $value) === 1,
            EvtAttributeRuleOperatorsEnum::STARTS_WITH->name => str_starts_with($value, $comparison_value),
            EvtAttributeRuleOperatorsEnum::ENDS_WITH->name => str_ends_with($value, $comparison_value),
            EvtAttributeRuleOperatorsEnum::CONTAINS->name => str_contains($value, $comparison_value),
            EvtAttributeRuleOperatorsEnum::ELEMENT_EXISTS->name => in_array($value, (array) $comparison_value),
            EvtAttributeRuleOperatorsEnum::KEY_EXISTS->name => array_key_exists($value, (array) $comparison_value),
            default => false,
        };
    }

    private function modify(
        $value,
        string $operator,
        $table_column,
        $filter_column = null,
        $filter_value = null,
        $comparison_value = null
    ) {

        [$table, $column] = explode('.', $table_column);

        $query = DB::table($table);

        if ($filter_column && $filter_value) {
            $query->where($filter_column, $filter_value);
        }

        return match ($operator) {
            EvtAttributeRuleOperatorsEnum::PLUS->name => $value + $comparison_value,
            EvtAttributeRuleOperatorsEnum::MINUS->name => $value - $comparison_value,
            EvtAttributeRuleOperatorsEnum::MULTIPLY->name => $value * $comparison_value,
            EvtAttributeRuleOperatorsEnum::DIVIDE->name => $value / $comparison_value,
            EvtAttributeRuleOperatorsEnum::MODULUS->name => $value % $comparison_value,
            EvtAttributeRuleOperatorsEnum::EXPONENTIATION->name => $value ** $comparison_value,
            EvtAttributeRuleOperatorsEnum::CONCATENATION->name => $value.$comparison_value,
            EvtAttributeRuleOperatorsEnum::MAX->name => $query->max($column),
            EvtAttributeRuleOperatorsEnum::MIN->name => $query->min($column),
            default => $value,
        };
    }
}
