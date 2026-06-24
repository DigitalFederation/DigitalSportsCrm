<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ExportExcelReadyNotification;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class BaseEnrollmentController extends Controller
{
    protected function applyEnrollmentFilters(Builder|HasMany $query, array $filters): Builder|HasMany
    {
        $query->when($filters['name'] ?? null, function ($q, $name) {
            $q->whereHas('individual', function ($q) use ($name) {
                $q->where(function ($q) use ($name) {
                    $q->where('name', 'like', "%{$name}%")
                        ->orWhere('surname', 'like', "%{$name}%");
                });
            });
        });

        $query->when($filters['gender'] ?? null, function ($q, $gender) {
            $q->whereHas('individual', function ($q) use ($gender) {
                $q->where('gender', $gender);
            });
        });

        $query->when($filters['discipline'] ?? null, function ($q, $disciplineId) {
            $q->where('discipline_id', $disciplineId);
        });

        $query->when($filters['enrolled_by'] ?? null, function ($q, $enrollableKey) {
            [$type, $id] = explode(':', $enrollableKey, 2);
            $q->whereHas('enrollment', function ($q) use ($type, $id) {
                $q->where('enrollable_type', $type)
                    ->where('enrollable_id', $id);
            });
        });

        $query->when($filters['status'] ?? null, function ($q, $status) {
            $q->where('status_class', $status);
        });

        return $query;
    }

    protected function getEnrolledByOptions(Event $event): array
    {
        return Enrollment::where('event_id', $event->id)
            ->select('enrollable_type', 'enrollable_id')
            ->distinct()
            ->with(['enrollable' => function ($query) {
                $query->withTrashed();
            }])
            ->get()
            ->mapWithKeys(function ($enrollment) {
                $key = $enrollment->enrollable_type . ':' . $enrollment->enrollable_id;
                $label = match ($enrollment->enrollable_type) {
                    'Domain\Federations\Models\Federation' => $enrollment->enrollable?->name ?? __('events.deleted_federation'),
                    'Domain\Entities\Models\Entity' => $enrollment->enrollable?->name ?? __('events.deleted_entity'),
                    'Domain\Individuals\Models\Individual' => trim(($enrollment->enrollable?->name ?? '') . ' ' . ($enrollment->enrollable?->surname ?? '')) ?: __('events.deleted_individual'),
                    default => __('events.unknown'),
                };

                return [$key => $label];
            })
            ->sort()
            ->all();
    }

    protected function getGenderOptions(): array
    {
        return [
            'M' => 'M',
            'F' => 'F',
        ];
    }

    protected function isDefaultFederation(): bool
    {
        return Auth::user()->federations->contains(fn ($f) => $f->is_default_federation);
    }

    protected function getNavigationLinks(Event $event): array
    {
        return [
            [
                'label' => __('events.athletes'),
                'color' => 'primary',
                'icon' => 'users',
                'links' => [
                    [
                        'url' => route('federation.evt-events.events.athlete-enrollment.registered', $event),
                        'text' => __('events.to_confirm'),
                        'key' => 'athlete.registered',
                    ],
                    [
                        'url' => route('federation.evt-events.events.athlete-enrollment.index', $event),
                        'text' => __('events.confirmed'),
                        'key' => 'athlete.index',
                    ],
                ],
            ],
            [
                'label' => __('events.coaches'),
                'color' => 'blue',
                'icon' => 'academic-cap',
                'links' => [
                    [
                        'url' => route('federation.evt-events.events.coach-enrollment.registered', $event),
                        'text' => __('events.to_confirm'),
                        'key' => 'coach.registered',
                    ],
                    [
                        'url' => route('federation.evt-events.events.coach-enrollment.index', $event),
                        'text' => __('events.confirmed'),
                        'key' => 'coach.index',
                    ],
                ],
            ],
            [
                'label' => __('events.team_officials'),
                'color' => 'emerald',
                'icon' => 'identification',
                'links' => [
                    [
                        'url' => route('federation.evt-events.events.officials-enrollment.registered', $event),
                        'text' => __('events.to_confirm'),
                        'key' => 'official.registered',
                    ],
                    [
                        'url' => route('federation.evt-events.events.officials-enrollment.index', $event),
                        'text' => __('events.confirmed'),
                        'key' => 'official.index',
                    ],
                ],
            ],
        ];
    }

    protected function generateExportFileName($event, $type)
    {
        return Str::slug($event->name) . '-' . $type . '-enrollments-' . now()->timestamp . '.xlsx';
    }

    protected function processExport(Request $request, $exportClass, $event, $type, $uniqueAttributes = null, $discipline = null)
    {

        $dataCount = $event->individualEnrollments()->count();

        $fileName = $this->generateExportFileName($event, $type);
        $filePath = 'secure_exports/' . $fileName;
        $userId = $request->user()->id;

        // Instantiate the export class with the required parameters
        $exportInstance = new $exportClass($event, $discipline);

        // Set unique attributes if provided
        if ($uniqueAttributes) {
            $exportInstance->setUniqueAttributes($uniqueAttributes);
        }

        if ($dataCount > 5000) {
            // If more than 5,000 items, queue for email
            $exportInstance->queue($filePath)->chain([
                function () use ($userId, $filePath, $fileName) {
                    $user = User::find($userId);
                    $user?->notify(new ExportExcelReadyNotification($filePath, $fileName));
                },
            ]);

            return redirect()->back()->with('success', 'Export is large and is being processed. It will be sent to your email shortly.');
        } else {
            // If 5,000 items or fewer, initiate direct download
            return Excel::download($exportInstance, $fileName);
        }
    }
}
