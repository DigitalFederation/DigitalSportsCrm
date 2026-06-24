<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CollectionExport implements FromArray, WithHeadings
{
    protected Collection $collection;
    protected array $columns;

    public function __construct(Collection $collection, array $columns)
    {
        $this->collection = $collection;
        $this->columns = $columns;
    }

    public function array(): array
    {
        return $this->collection->toArray();
    }

    public function map($item): array
    {
        $mapped = [];

        foreach ($this->columns as $column) {
            $mapped[] = $item[$column];
        }

        return $mapped;
    }

    public function headings(): array
    {
        return $this->columns;
    }
}
