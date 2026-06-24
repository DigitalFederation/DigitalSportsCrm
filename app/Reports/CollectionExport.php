<?php

namespace App\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CollectionExport implements FromCollection, WithHeadings, WithMapping
{
    protected $collection;

    protected $columns;

    public function __construct($collection, $columns)
    {
        $this->collection = $collection;
        $this->columns = $columns;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function map($row): array
    {
        // Map the data row to the columns
        // ...
    }

    public function headings(): array
    {
        return $this->columns;
    }
}
