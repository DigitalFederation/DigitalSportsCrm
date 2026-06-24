<?php

namespace App\Reports;

interface ReportTemplate
{
    public function query($filters);

    public function columns();
}
