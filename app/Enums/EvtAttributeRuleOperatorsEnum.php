<?php

namespace App\Enums;

enum EvtAttributeRuleOperatorsEnum: string
{
    // Comparison Operators
    // Comparison Operators
    case EQUAL = '==';
    case NOT_EQUAL = '!=';
    case IDENTICAL = '===';
    case NOT_IDENTICAL = '!==';
    case GREATER_THAN = '>';
    case LESS_THAN = '<';
    case GREATER_THAN_OR_EQUAL = '>=';
    case LESS_THAN_OR_EQUAL = '<=';

    // Arithmetic Operators
    case PLUS = '+';
    case MINUS = '-';
    case MULTIPLY = '*';
    case DIVIDE = '/';
    case MODULUS = '%';
    case EXPONENTIATION = '**';

    // Logical Operators
    case AND = '&&';
    case OR = '||';
    case NOT = '!';
    case XOR = 'xor';

    // String Operators
    case CONCATENATION = '.';
    case SPACESHIP = '<=>';

    // Array Operators
    case ELEMENT_EXISTS = 'in_array';
    case KEY_EXISTS = 'array_key_exists';

    // Special Operators
    case REGEX_MATCH = 'preg_match';
    case STARTS_WITH = 'starts_with';
    case ENDS_WITH = 'ends_with';
    case CONTAINS = 'contains';

    case MAX = 'max';
    case MIN = 'min';

    case MAX_OCCURRENCES = 'MAX_OCCURRENCES';

    public function toString(): string
    {
        return match ($this) {
            self::EQUAL => 'Equal (==)',
            self::NOT_EQUAL => 'Not Equal (!=)',
            self::IDENTICAL => 'Identical (===)',
            self::NOT_IDENTICAL => 'Not Identical (!==)',
            self::GREATER_THAN => 'Greater Than (>)',
            self::LESS_THAN => 'Less Than (<)',
            self::GREATER_THAN_OR_EQUAL => 'Greater Than or Equal (>=)',
            self::LESS_THAN_OR_EQUAL => 'Less Than or Equal (<=)',
            self::PLUS => 'Addition (+)',
            self::MINUS => 'Subtraction (-)',
            self::MULTIPLY => 'Multiplication (*)',
            self::DIVIDE => 'Division (/)',
            self::MODULUS => 'Modulus (%)',
            self::EXPONENTIATION => 'Exponentiation (**)',
            self::AND => 'Logical AND (&&)',
            self::OR => 'Logical OR (||)',
            self::NOT => 'Logical NOT (!)',
            self::XOR => 'Logical XOR (xor)',
            self::CONCATENATION => 'Concatenation (.)',
            self::SPACESHIP => 'Spaceship (<=>)',
            self::ELEMENT_EXISTS => 'Element Exists (in_array)',
            self::KEY_EXISTS => 'Key Exists (array_key_exists)',
            self::REGEX_MATCH => 'Regex Match (preg_match)',
            self::STARTS_WITH => 'Starts With (starts_with)',
            self::ENDS_WITH => 'Ends With (ends_with)',
            self::CONTAINS => 'Contains (contains)',
            self::MAX => 'Maximum (max)',
            self::MIN => 'Minimum (min)',
            self::MAX_OCCURRENCES => 'Max Occurrences',
        };
    }

    public static function getEnumFromValue(string $value): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }

        return null;
    }

    public static function getEnumFromName(string $name): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        return null;
    }

    public static function getValueFromName(string $name)
    {
        foreach (EvtAttributeRuleOperatorsEnum::cases() as $case) {
            if ($case->name === $name) {
                return $case->value;
            }
        }
    }
}
