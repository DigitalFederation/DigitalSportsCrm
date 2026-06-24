<?php

namespace App\Livewire\EvtEvents\Concerns;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use Filament\Tables\Columns\TextColumn;

trait HasEnrollmentTableHelpers
{
    protected static function formatGender(?string $state): string
    {
        return match ($state) {
            'male' => 'M',
            'female' => 'F',
            default => '-',
        };
    }

    protected static function genderColor(?string $state): string
    {
        return match ($state) {
            'male' => 'info',
            'female' => 'success',
            default => 'gray',
        };
    }

    protected function makeGenderColumn(): TextColumn
    {
        return TextColumn::make('individual.gender')
            ->label(__('events.gender'))
            ->formatStateUsing(fn (?string $state): string => self::formatGender($state))
            ->badge()
            ->color(fn (?string $state): string => self::genderColor($state))
            ->alignCenter();
    }

    protected function getEnrollmentStatusLabel(string $statusClass): string
    {
        return match ($statusClass) {
            EvtAthleteEnrollmentStatusEnum::COMPLETED->value => __('events.enrollment_status_confirmed'),
            default => __('events.enrollment_status_enrolled'),
        };
    }

    protected function getEnrollmentStatusColor(string $statusClass): string
    {
        return match ($statusClass) {
            EvtAthleteEnrollmentStatusEnum::COMPLETED->value => 'success',
            default => 'info',
        };
    }

    protected function getStaffStatusLabel(string $statusClass): string
    {
        return match (true) {
            str_contains($statusClass, 'Assigned') => __('events.enrollment_status_confirmed'),
            default => __('events.enrollment_status_enrolled'),
        };
    }

    protected function getStaffStatusColor(string $statusClass): string
    {
        return match (true) {
            str_contains($statusClass, 'Assigned') => 'success',
            default => 'info',
        };
    }

    protected function buildNonAthleteAttributeColumns(string $enrollmentType): array
    {
        $columns = [];
        $uniqueAttributes = $this->getUniqueAttributesFromEnrollments($enrollmentType);
        $systemTypes = ['OUTOFRACE', 'HIDDEN'];

        foreach ($uniqueAttributes as $attribute) {
            $attrId = $attribute['id'];
            $attrType = strtoupper($attribute['type'] ?? 'TEXT');

            if (in_array($attrType, $systemTypes) || $attribute['is_global']) {
                continue;
            }

            $column = TextColumn::make('attr_' . $attrId)
                ->label($attribute['name'])
                ->getStateUsing(function ($record) use ($attrId) {
                    $attr = $record->attributes->firstWhere('attribute_id', $attrId);

                    return $attr?->value ?: '-';
                })
                ->alignCenter();

            if (in_array($attrType, ['TIME', 'BESTTIME'])) {
                $column->fontFamily('mono');
            }

            $columns[] = $column;
        }

        return $columns;
    }

    protected function getUniqueAttributesFromEnrollments(string $enrollmentType): array
    {
        $query = $this->getEnrollmentQueryForType($enrollmentType);

        if (! $query) {
            return [];
        }

        $uniqueAttributes = [];
        $seenIds = [];

        foreach ($query->get() as $enrollment) {
            foreach ($enrollment->attributes as $enrollmentAttr) {
                if ($enrollmentAttr->attribute && ! in_array($enrollmentAttr->attribute_id, $seenIds)) {
                    $seenIds[] = $enrollmentAttr->attribute_id;
                    $uniqueAttributes[] = [
                        'id' => $enrollmentAttr->attribute_id,
                        'name' => $enrollmentAttr->attribute->name,
                        'type' => $enrollmentAttr->attribute->attribute_type ?? 'TEXT',
                        'is_global' => (bool) $enrollmentAttr->attribute->fillable_global,
                    ];
                }
            }
        }

        return $uniqueAttributes;
    }

    protected function hasDetailAttributes($record): bool
    {
        $detailAttributes = $this->getDetailAttributes($record);

        return ! empty($detailAttributes['status']) || ! empty($detailAttributes['global']);
    }

    protected function getDetailAttributes($record): array
    {
        $attributes = [
            'status' => [],
            'global' => [],
            'other' => [],
        ];

        foreach ($record->attributes as $enrollmentAttr) {
            if (! $enrollmentAttr->attribute || empty($enrollmentAttr->value)) {
                continue;
            }

            $attrType = strtoupper($enrollmentAttr->attribute->attribute_type ?? 'TEXT');
            $isGlobal = (bool) $enrollmentAttr->attribute->fillable_global;

            if ($attrType === 'OUTOFRACE') {
                $attributes['status'][] = [
                    'name' => $enrollmentAttr->attribute->name,
                    'value' => $enrollmentAttr->value,
                ];
            } elseif ($isGlobal) {
                $attributes['global'][] = [
                    'name' => $enrollmentAttr->attribute->name,
                    'value' => $enrollmentAttr->value,
                    'type' => $attrType,
                ];
            }
        }

        return $attributes;
    }

    /**
     * Override this in each component to map enrollment type strings to queries.
     */
    abstract protected function getEnrollmentQueryForType(string $enrollmentType): ?\Illuminate\Database\Eloquent\Builder;
}
