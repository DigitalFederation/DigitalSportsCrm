<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Http\Controllers\Controller;
use App\Services\EventMasterListColumnDefinitionService;
use Domain\EvtEvents\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventMasterController extends Controller
{
    protected $columnDefinitionService;

    public function __construct(EventMasterListColumnDefinitionService $columnDefinitionService)
    {
        $this->columnDefinitionService = $columnDefinitionService;
    }

    public function index()
    {
        $columnDefinitions = $this->columnDefinitionService->getColumnDefinitions();

        return view('web.admin.evt_events.events.master', compact('columnDefinitions'));
    }

    public function getEvents(Request $request)
    {
        $query = Event::with([
            'sport',
            'venueCountry',
            'geoZones',
            'subRegions',
            'countries',
            'organizer.organizable',
            'organizerDetails',
            'competition.sport',
            'competition.types',
            'competition.antiDopingRecord',
            'competition.technicalDelegates.federation',
            'competition.venueCountry',
            'competition.disciplineTemplate.disciplines',
        ]);

        // Apply sorting
        if ($request->has('sort')) {
            $sort = json_decode($request->input('sort'));
            foreach ($sort as $column) {
                $query->orderBy($column->field, $column->dir);
            }
        }

        $page = $request->input('page', 1);
        $size = $request->input('size', 50);

        $total = $query->count();
        $events = $query->skip(($page - 1) * $size)
            ->take($size)
            ->get();

        \Log::debug('Events data:', $events->toArray());

        return response()->json([
            'data' => $events,
            'last_page' => ceil($total / $size),
            'total' => $total,
        ]);
    }

    public function updateEvent(Request $request)
    {
        // $validated = $request->validated();

        try {
            DB::beginTransaction();

            $event = Event::with([
                'sport',
                'venueCountry',
                'geoZones',
                'subRegions',
                'countries',
                'organizer.organizable',
                'organizerDetails',
                'competition.sport',
                'competition.types',
                'competition.antiDopingRecord',
                'competition.technicalDelegates.federation',
                'competition.venueCountry',
                'competition.disciplineTemplate.disciplines',
            ])->findOrFail($request['id']);

            $field = $request['field'];
            $value = $request['value'];
            $oldValue = $this->getOldValue($event, $field);

            $parts = explode('.', $field);
            $modelInstance = $event;

            for ($i = 0; $i < count($parts) - 1; $i++) {
                $relation = $parts[$i];
                if ($relation === 'competition') {
                    if (! $modelInstance->competition) {
                        $modelInstance->competition()->create();
                        $modelInstance->refresh();
                    }
                    $modelInstance = $modelInstance->competition;
                } elseif ($relation === 'technical_delegates') {

                    if (! $modelInstance->technicalDelegates->count()) {
                        $modelInstance->technicalDelegates()->create();
                        $modelInstance->refresh();
                    }
                    // Access the first technical delegate using first()
                    $modelInstance = $modelInstance->technicalDelegates->first();
                    if (! $modelInstance) {
                        throw new \Exception('Unable to find or create technical delegate');
                    }
                    $i++;
                } elseif ($relation === 'organizer_details' || $relation === 'organizerDetails') {
                    if (! $modelInstance->organizerDetails) {
                        $modelInstance->organizerDetails()->create();
                        $modelInstance->refresh();
                    }
                    $modelInstance = $modelInstance->organizerDetails;
                } elseif ($relation === 'anti_doping_record') {
                    if (! $modelInstance->antiDopingRecord) {
                        $modelInstance->antiDopingRecord()->create();
                        $modelInstance->refresh();
                    }
                    $modelInstance = $modelInstance->antiDopingRecord;
                } else {
                    if (! $modelInstance->$relation) {
                        $modelInstance->$relation()->create();
                        $modelInstance->refresh();
                    }
                    $modelInstance = $modelInstance->$relation;
                }
            }

            $finalField = end($parts);

            if (! $modelInstance) {
                throw new \Exception("Unable to find or create the related model for field: $field");
            }

            $modelInstance->$finalField = $value;
            $modelInstance->save();

            // Log the activity
            activity()
                ->performedOn($event)
                ->withProperties([
                    'field' => $field,
                    'old_value' => $oldValue,
                    'new_value' => $value,
                ])
                ->log("Updated {$field} for event: {$event->name}");

            DB::commit();

            return response()->json(['message' => 'Updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return response()->json(['message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    private function getOldValue($event, $field)
    {
        $parts = explode('.', $field);
        $modelInstance = $event;

        foreach ($parts as $part) {
            if (is_null($modelInstance)) {
                return null;
            }
            if (is_array($modelInstance) || $modelInstance instanceof \Illuminate\Support\Collection) {
                // If it's an array or collection, try to access the first item
                $modelInstance = $modelInstance[0] ?? null;
                if (is_null($modelInstance)) {
                    return null;
                }
            }
            $modelInstance = $modelInstance->$part ?? null;
        }

        return $modelInstance;
    }
}
