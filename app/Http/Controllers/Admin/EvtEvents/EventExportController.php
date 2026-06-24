<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Exports\EventExport;
use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class EventExportController extends Controller
{
    /**
     * Show the form for creating a new event.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function store(Event $event, Request $request)
    {
        $cacheKey = "event_export_{$event->id}";
        $cachedFile = Cache::get($cacheKey);
        if ($cachedFile) {
            return response()->download($cachedFile);
        }

        return Excel::download(new EventExport($event->id), 'event-data-'.$event->id.'.xlsx');
    }
}
