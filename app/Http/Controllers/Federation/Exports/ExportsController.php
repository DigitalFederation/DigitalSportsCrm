<?php

namespace App\Http\Controllers\Federation\Exports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportsController extends Controller
{
    public function store(Request $request)
    {

        $filename = decrypt($request->input('filename'));

        $file = Storage::disk('local')->get($filename);

        return response()->streamDownload(
            fn () => print ($file),
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
