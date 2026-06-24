<?php

namespace App\Http\Controllers\Federation\Exports;

use App\Exports\FederationIndividualsExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class IndividualExportController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Queue the export and redirect with a success message
            $export = new FederationIndividualsExport;
            $exportFileName = 'export-individuals-' . now()->format('Y-m-d-His') . '.xlsx';

            // Return the Excel file as download response
            return Excel::download($export, $exportFileName);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());

            // In case of an error, redirect back with an error message
            return back()->with('error', 'Failed to start the export. Please try again.');
        }

    }
}
