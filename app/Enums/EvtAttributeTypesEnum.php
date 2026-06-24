<?php

namespace App\Enums;

/**
 * @method string label()
 * @method bool isText()
 * @method bool isTextarea()
 * @method bool isSelect()
 * @method bool isCountry()
 * @method bool isMemberCode()
 * @method bool isBestTime()
 * @method bool isBirthdate()
 * @method bool isTime()
 * @method bool isHidden()
 * @method bool isOutOfRace()
 */
enum EvtAttributeTypesEnum: string
{
    case TEXT = 'TEXT';
    case TEXTAREA = 'TEXTAREA';
    case SELECT = 'SELECT';
    case COUNTRY = 'COUNTRY';
    case MEMBERCODE = 'MEMBERCODE';
    case BESTTIME = 'BESTTIME';
    case BIRTHDATE = 'BIRTHDATE';
    case TIME = 'TIME';
    case HIDDEN = 'HIDDEN';
    case OUTOFRACE = 'OUTOFRACE';

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::TEXTAREA => 'Text Area',
            self::SELECT => 'Select List',
            self::COUNTRY => 'Country',
            self::MEMBERCODE => 'International Code',
            self::BESTTIME => 'Best Time',
            self::BIRTHDATE => 'Date of Birth',
            self::TIME => 'Time',
            self::HIDDEN => 'Hidden',
            self::OUTOFRACE => 'Out of Race',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::TEXT => 'Single line text input',
            self::TEXTAREA => 'Multi-line text input',
            self::SELECT => 'Dropdown selection list',
            self::COUNTRY => 'Country selection',
            self::MEMBERCODE => 'CMAS identification code',
            self::BESTTIME => 'Best achieved time',
            self::BIRTHDATE => 'Date of birth selector',
            self::TIME => 'Time input',
            self::HIDDEN => 'Hidden field for programmatic operations',
            self::OUTOFRACE => 'Indicates if enrollment counts towards official limit',
        };
    }

    /**
     * Check if the enum case matches a specific type
     */
    public function is(self $type): bool
    {
        return $this === $type;
    }

    /**
     * Magic method to handle isXxx() calls
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (str_starts_with($name, 'is')) {
            $case = strtoupper(substr($name, 2));

            return $this->name === $case;
        }

        throw new \BadMethodCallException("Method {$name} does not exist");
    }

    /**
     * Get all possible values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get value => label pairs
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->label(),
        ])->all();
    }

    /**
     * Find enum case by label
     */
    public static function fromLabel(string $label): ?self
    {
        return collect(self::cases())
            ->first(fn ($case) => $case->label() === $label);
    }

    /**
     * Get grouped options (useful for form selects)
     */
    public static function groupedOptions(): array
    {
        return [
            'Text Inputs' => [
                self::TEXT->value => self::TEXT->label(),
                self::TEXTAREA->value => self::TEXTAREA->label(),
            ],
            'Special Fields' => [
                self::SELECT->value => self::SELECT->label(),
                self::COUNTRY->value => self::COUNTRY->label(),
                self::MEMBERCODE->value => self::MEMBERCODE->label(),
            ],
            'Time Related' => [
                self::BESTTIME->value => self::BESTTIME->label(),
                self::BIRTHDATE->value => self::BIRTHDATE->label(),
                self::TIME->value => self::TIME->label(),
            ],
            'System' => [
                self::HIDDEN->value => self::HIDDEN->label(),
                self::OUTOFRACE->value => self::OUTOFRACE->label(),
            ],
        ];
    }
}
