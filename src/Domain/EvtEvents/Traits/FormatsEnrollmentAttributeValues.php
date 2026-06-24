<?php

namespace Domain\EvtEvents\Traits;

/**
 * Trait for formatting enrollment attribute values before saving.
 * Handles time/besttime formatting to standardized format.
 */
trait FormatsEnrollmentAttributeValues
{
    public function setAttribute($key, $value)
    {
        if ($key === 'value') {
            // Load attribute relationship if not loaded
            if (! $this->relationLoaded('attribute')) {
                $this->load('attribute');
            }

            // Only format if attribute exists and is time/besttime type
            $timeTypes = ['time', 'TIME', 'besttime', 'BESTTIME'];
            if ($this->attribute && in_array($this->attribute->attribute_type, $timeTypes)) {
                $value = $this->formatTimeValue($value);
            }
        }

        return parent::setAttribute($key, $value);
    }

    private function formatTimeValue($value)
    {
        // Handle MM:SS.ms format (from time input component: e.g., "01:30.50")
        if (preg_match('/^(\d{1,2}):(\d{1,2})[.,](\d{2})$/', $value, $matches)) {
            $minutes = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $seconds = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $hundredths = $matches[3];

            return "00:{$minutes}:{$seconds}.{$hundredths}";
        }

        // Handle HH:MM:SS.ms format (full format with hours)
        if (preg_match('/^(\d{1,2}):(\d{1,2}):(\d{1,2})[.,](\d{2})$/', $value, $matches)) {
            $hours = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $minutes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $seconds = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
            $hundredths = $matches[4];

            return "{$hours}:{$minutes}:{$seconds}.{$hundredths}";
        }

        // Handle SS.ms format (just seconds and hundredths)
        if (preg_match('/^(\d{1,2})[.,](\d{2})$/', $value, $matches)) {
            $seconds = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $hundredths = $matches[2];

            return "00:00:{$seconds}.{$hundredths}";
        }

        return $value;
    }
}
