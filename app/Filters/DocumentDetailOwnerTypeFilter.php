<?php

// app/Filters/DocumentDetailOwnerTypeFilter.php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class DocumentDetailOwnerTypeFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        // Assuming $value is the full class name of the owner_type you want to filter by
        return $query->whereHas('details', function (Builder $query) use ($value) {
            $query->where('owner_type', $value);
        });
    }
}
