<?php

namespace App\QueryFilters;

use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Custom filter that matches member names where the search term
 * appears at the start of a word (not in the middle).
 *
 * Uses whereHasMorph to handle polymorphic member relationship safely,
 * since entities only have 'name' while individuals also have 'surname'.
 *
 * Example: searching for "Ana" matches:
 * - "Ana" (exact)
 * - "Ana Maria" (starts with)
 * - "Maria Ana" (word starts with Ana)
 *
 * But NOT:
 * - "Liliana" (Ana is in the middle of a word)
 */
class FilterMemberNameByWordStart implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (empty($value)) {
            return;
        }

        $escapedValue = addcslashes($value, '%_');

        $query->whereHasMorph('member', ['*'], function (Builder $q, string $type) use ($escapedValue) {
            $q->where(function (Builder $subQuery) use ($escapedValue, $type) {
                $subQuery->where('name', 'like', $escapedValue . '%')
                    ->orWhere('name', 'like', '% ' . $escapedValue . '%');

                if ($type === Individual::class) {
                    $subQuery->orWhere('surname', 'like', $escapedValue . '%')
                        ->orWhere('surname', 'like', '% ' . $escapedValue . '%');
                }
            });
        });
    }
}
