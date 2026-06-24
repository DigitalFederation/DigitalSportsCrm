<?php

namespace Domain\Individuals\Actions;

use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SearchIndividualsAction
{
    /**
     * Search for Individuals by international code, name, or email.
     * This is only to be used by admin user
     */
    public function __invoke(string $searchTerm): Collection
    {
        $results = Individual::query()
            ->where(function (Builder $query) use ($searchTerm) {
                $query->where('member_code', 'like', "%{$searchTerm}%")
                    ->orWhere('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            })
            ->limit(10)
            ->get();

        return $results;
    }
}
