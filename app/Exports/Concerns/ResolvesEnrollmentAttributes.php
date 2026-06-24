<?php

namespace App\Exports\Concerns;

use Illuminate\Support\Collection;

trait ResolvesEnrollmentAttributes
{
    /**
     * Resolve unique non-system, non-global attributes from a collection of enrollments.
     *
     * @return array<int, array{id: int, name: string}>
     */
    protected function resolveAttributes(): array
    {
        return self::resolveEnrollmentAttributes($this->enrollments);
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    protected static function resolveEnrollmentAttributes(Collection $enrollments): array
    {
        $unique = [];
        $seenIds = [];
        $systemTypes = ['OUTOFRACE', 'HIDDEN'];

        foreach ($enrollments as $enrollment) {
            foreach ($enrollment->attributes as $enrollmentAttr) {
                if ($enrollmentAttr->attribute
                    && ! in_array($enrollmentAttr->attribute_id, $seenIds)
                    && ! in_array(strtoupper($enrollmentAttr->attribute->attribute_type ?? 'TEXT'), $systemTypes)
                    && ! (bool) $enrollmentAttr->attribute->fillable_global
                ) {
                    $seenIds[] = $enrollmentAttr->attribute_id;
                    $unique[] = [
                        'id' => $enrollmentAttr->attribute_id,
                        'name' => $enrollmentAttr->attribute->name,
                    ];
                }
            }
        }

        return $unique;
    }
}
