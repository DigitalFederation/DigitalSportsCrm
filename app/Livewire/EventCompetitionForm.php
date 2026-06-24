<?php

namespace App\Livewire;

use App\Enums\EvtAttachmentTypes;
use App\Enums\EvtCompetitionAgeCategoryEnum;
use App\Enums\EvtCompetitionCategoryEnum;
use App\Enums\EvtCompetitionEnvironmentEnum;
use App\Enums\EvtCompetitionStatusEnum;
use App\Enums\EvtCompetitionTypeEnum;
use App\Models\Country;
use Carbon\Carbon;
use Domain\EvtEvents\Models\AntiDoping;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\CompetitionType;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Sport;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EventCompetitionForm extends Component
{
    use WithFileUploads;

    public $attachments = [];

    public $full_name;

    public $event_id;

    public $number;

    public $sport_id;

    public $competition_id;

    public $rounds_total;

    public $competition_types = [];

    public $cat_age;

    public $cat_competition;

    public $environment;

    public $environment_options;

    public $venue;

    public $venue_address;

    public $competition_start_date;

    public $competition_end_date;

    public $registration_start_date;

    public $registration_end_date;

    public $status_options;

    public $status_class;

    public $competition_type_options;

    public $sport_options;

    public $n_control_planned;

    public $n_control;

    public $medals_gold;

    public $medals_silver;

    public $medals_bronze;

    public $cat_age_options;

    public $cat_competition_options;

    public $countries;

    public $venue_country;

    public $venue_city;

    public $disciplines;

    public $moloni_reference;

    // Technical Delegate

    protected $rules = [
        'full_name' => 'string|max:255',
        'number' => 'required|numeric',
        'sport_id' => 'required|exists:sports,id',
        'rounds_total' => 'nullable|numeric',
        'competition_types' => 'required',
        'cat_age' => 'nullable|string',
        'cat_competition' => 'nullable|string',
        'environment' => 'nullable|string',
        'status_class' => 'nullable|string',
        'venue' => 'nullable|string',
        'venue_address' => 'nullable|string',
        'venue_city' => 'nullable|string',
        'venue_country' => 'nullable|int|exists:country,id',
        'competition_start_date' => 'nullable|date|before_or_equal:competition_end_date',
        'competition_end_date' => 'nullable|date|after_or_equal:competition_start_date',
        'registration_start_date' => 'nullable|date|before_or_equal:registration_end_date',
        'registration_end_date' => 'nullable|date|after_or_equal:registration_start_date',
        'n_control_planned' => 'nullable|numeric|min:0',
        'n_control' => 'nullable|numeric|min:0',
        'moloni_reference' => 'nullable|string|max:50',
    ];

    public function mount(Competition $competition): void
    {
        $this->competition_id = $competition->id;
        $this->event_id = $competition->event_id;
        $this->full_name = $competition->full_name;
        $this->number = $competition->number;
        $this->competition_start_date = $competition->start_date;
        $this->competition_end_date = $competition->end_date;
        $this->registration_start_date = $competition->event->start_registration ? Carbon::parse($competition->event->start_registration)->format('Y-m-d') : null;
        $this->registration_end_date = $competition->event->start_registration ? Carbon::parse($competition->event->end_registration)->format('Y-m-d') : null;
        $this->sport_id = $competition->sport_id;
        $this->competition_types = $competition->types()->pluck('competition_type')->toArray();
        $this->rounds_total = $competition->rounds_total;
        $this->cat_age = $competition->cat_age;
        $this->cat_competition = $competition->cat_competition;
        $this->environment = $competition->environment;
        $this->status_class = $competition->status_class;
        $this->venue = $competition->venue;
        $this->venue_address = $competition->venue_address;
        $this->venue_city = $competition->venue_city;
        $this->venue_country = $competition->venue_country_id;
        $this->medals_gold = $competition->medals_gold;
        $this->medals_silver = $competition->medals_silver;
        $this->medals_bronze = $competition->medals_bronze;
        $this->moloni_reference = $competition->moloni_reference;

        $this->n_control_planned = $competition->antiDopingRecords->first()?->num_controls_planned;
        $this->n_control = $competition->antiDopingRecords->first()?->number_of_controls;

        $this->countries = Country::select('id', 'name')->get()->pluck('name', 'id');
        $this->sport_options = Sport::select('id', 'name')->get()->pluck('name', 'id');
        $this->status_options = EvtCompetitionStatusEnum::cases();
        $this->environment_options = EvtCompetitionEnvironmentEnum::cases();
        $this->competition_type_options = $this->competitionTypesFillList();
        $this->cat_age_options = EvtCompetitionAgeCategoryEnum::cases();
        $this->cat_competition_options = EvtCompetitionCategoryEnum::cases();

        foreach (Event::find($this->event_id)->getMedia(EvtAttachmentTypes::active->name) as $media) {
            $this->attachments[] = [
                'name' => $media->name,
                'id' => $media->id,
                'file' => null,
            ];
        }
        $this->disciplines = $competition->disciplines;

    }

    public function competitionTypesFillList(): array
    {
        $list = [];
        foreach (EvtCompetitionTypeEnum::cases() as $type) {
            $list[$type->name] = $type->value;
        }

        return $list;
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $data = [
                'event_id' => $this->event_id,
                'number' => $this->number,
                'sport_id' => $this->sport_id,
                'rounds_total' => $this->rounds_total,
                'cat_age' => $this->cat_age,
                'cat_competition' => $this->cat_competition,
                'environment' => $this->environment,
                'full_name' => $this->full_name,
                'status_class' => $this->status_class,
                'venue' => $this->venue,
                'venue_address' => $this->venue_address,
                'venue_city' => $this->venue_city,
                'venue_country_id' => $this->venue_country,
                'start_date' => Carbon::parse($this->competition_start_date)->format('Y-m-d'),
                'end_date' => Carbon::parse($this->competition_end_date)->format('Y-m-d'),
                'medals_gold' => $this->medals_gold,
                'medals_silver' => $this->medals_silver,
                'medals_bronze' => $this->medals_bronze,
                'moloni_reference' => $this->moloni_reference,
            ];

            if (isset($this->competition_id)) {
                $competition = Competition::find($this->competition_id);
                $competition->update($data);
                $competition->event->start_registration = $this->registration_start_date;
                $competition->event->end_registration = $this->registration_end_date;
                $competition->event->save();

                foreach ($this->attachments as $attachment) {
                    if (empty($attachment['id'])) {
                        $tempFilePath = $attachment['file']->getRealPath();
                        // Using Spatie Media Library to add media
                        if (file_exists($tempFilePath)) {
                            $extensionFile = pathinfo($attachment['file']->getClientOriginalName(), PATHINFO_EXTENSION);
                            Event::find($this->event_id)->addMedia($tempFilePath)
                                ->usingName($attachment['name'])
                                ->usingFileName(Str::slug($attachment['name'], '_').'.'.$extensionFile)
                                ->toMediaCollection(EvtAttachmentTypes::active->name);
                        }
                    }
                }

                $message = sprintf('Competition %s successfully updated', $this->full_name);
            } else {
                $competition = Competition::create($data);
                $competition->event->start_registration = $this->registration_start_date;
                $competition->event->end_registration = $this->registration_end_date;
                $competition->event->save();

                // Handle file uploads using Spatie Media Library
                foreach ($this->attachments as $attachment) {
                    $tempFilePath = $attachment['file']->getRealPath();
                    // Using Spatie Media Library to add media
                    if (file_exists($tempFilePath)) {
                        $extensionFile = pathinfo($attachment['file']->getClientOriginalName(), PATHINFO_EXTENSION);
                        Event::find($this->event_id)->addMedia($tempFilePath)
                            ->usingName($attachment['name'])
                            ->usingFileName(Str::slug($attachment['name'], '_').'.'.$extensionFile)
                            ->toMediaCollection(EvtAttachmentTypes::active->name);
                    }
                }

                $message = sprintf('Competition %s successfully created', $this->full_name);
            }

            CompetitionType::where('competition_id', $competition->id)->delete();
            foreach ($this->competition_types as $type) {
                CompetitionType::create([
                    'competition_id' => $competition->id,
                    'competition_type' => $type,
                ]);
            }

            AntiDoping::updateOrCreate(
                [
                    'competition_id' => $this->competition_id,
                ],
                [
                    'num_controls_planned' => $this->n_control_planned,
                    'number_of_controls' => $this->n_control,
                    'date_updated' => now(),
                ]);

            DB::commit();

            return redirect()->route('admin.evt-events.events.show', $this->event_id)->with('success', $message);

        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());

            return redirect()->route('admin.evt-events.events.index')->with('error', "Error: {$e->getMessage()}");
        }

    }

    #[On('selectedMultipleUpdatedValue.competition_types')]
    public function updatedCompetitionTypes($values): void
    {
        $this->competition_types = $values;
    }

    public function addAttachment()
    {
        $this->attachments[] = ['file' => null, 'name' => ''];
    }

    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index]['id'])) {
            $mediaItem = Media::find($this->attachments[$index]['id']);
            if ($mediaItem) {
                $mediaItem->delete();
            }
        }

        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function render()
    {
        return view('livewire.event-competition-form');
    }
}
