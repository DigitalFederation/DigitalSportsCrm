<?php

namespace App\Livewire\Attachments;

use App\Events\AttachmentFileCreatedEvent;
use App\Models\Language;
use Domain\Attachments\Models\Attachment;
use Domain\Entities\Actions\GetEntitiesFromLicensesAction;
use Domain\Entities\Models\Entity;
use Domain\Federations\Actions\GetFederationsFromLicensesAction;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\GetIndividualsFromCertificationsAction;
use Domain\Individuals\Actions\GetIndividualsFromLicensesAction;
use Domain\Individuals\Actions\GetIndividualsFromProfessionalRolesAction;
use Domain\Users\Actions\GetUserTypeAction;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class AttachmentFederationCreate extends Component
{
    use WithFileUploads;

    public $model;

    public $attachments;

    public $attachment_name;

    public $categories;

    public $selected_category = '';

    public $message;

    public $message_type = null;

    public $message_title = null;

    public $message_body = null;

    public $countries;

    public $languages;

    public $selected_language = '';

    public $committee;

    public $professional_roles;

    public $licenses;

    public $entity_licenses;

    public $individual_licenses;

    public $federations;

    public $recipient;

    public $recipients = [
        'all_entities' => 'All Entities',
        'all_individuals' => 'All Individuals',
        'all_entities_&_individuals' => 'All Entities & Individuals',
        'individual' => 'Filter Individuals',
        'entity' => 'Filter Entities',
    ];

    public $federations_preview = [];

    public $federation_licenses;

    public $federation_countries;

    public $validationErrors = [];

    public $federation_federations = [];

    public $certifications = [];

    public $entities_federations;

    public $entities_licenses;

    public $entities_preview_count = 0;

    public $individuals_federations;

    public $individuals_licenses;

    public $individuals_certifications;

    public $individuals_professional_roles;

    public $individuals_preview_count = 0;

    public function render()
    {
        return view('livewire.attachments.attachment-federation-create');
    }

    public function mount($committee, $countries, $categories, $professional_roles, $licenses, $entity_licenses, $individual_licenses, $federation_id, $certifications)
    {
        // Fetch languages here
        $this->languages = Language::orderBy('name')->get(['id', 'name'])
            ->prepend(['id' => 'all', 'name' => 'All Languages'])
            ->toArray();
    }

    /**
     * As documented on:
     * https://livewire.laravel.com/docs/events#listening-for-events
     */

    /**
     * Federations Listeners
     */
    #[On('selectedMultipleUpdatedValue.federation_licenses')]
    public function updateFederationLicenses($values)
    {
        $this->federation_licenses = $values;
        $this->getFederationsFromLicenses();
    }

    #[On('selectedMultipleUpdatedValue.federation_countries')]
    public function updateFederationCountries($values)
    {
        $this->federation_countries = $values;
        $this->getFederationsByCountryId();
    }

    #[On('selectedMultipleUpdatedValue.federation_federations')]
    public function updateFederationFederations($values)
    {
        $this->federation_federations = $values;
    }

    /**
     * Entities Listeners
     */
    #[On('selectedMultipleUpdatedValue.entities_federations')]
    public function updateEntitiesFederations($values)
    {
        $this->entities_federations = $values;
        $this->getEntitiesByFederation($values);
    }

    #[On('selectedMultipleUpdatedValue.entities_licenses')]
    public function updateEntitiesLicenses($values)
    {
        $this->entities_licenses = $values;
        $this->getEntitiesFromLicenses($values);
    }

    #[On('selectedMultipleUpdatedValue.individuals_federations')]
    public function updateIndividualFederations($values)
    {
        $this->individuals_federations = $values;
        $this->getIndividualsByFederation($values);
    }

    #[On('selectedMultipleUpdatedValue.individuals_licenses')]
    public function updateIndividualLicenses($values)
    {
        $this->individuals_licenses = $values;
        $this->getIndividualsFromLicenses($values);
    }

    #[On('selectedMultipleUpdatedValue.individuals_certifications')]
    public function updateIndividualCertifications($values)
    {
        $this->individuals_certifications = $values;
        $this->getIndividualsFromCertifications($values);
    }

    #[On('selectedMultipleUpdatedValue.individuals_professional_roles')]
    public function updateIndividualProfessionalRoles($values)
    {
        $this->individuals_professional_roles = $values;
        $this->getIndividualsFromProfessionalRoles($values);
    }

    public function getFederationsFromLicenses()
    {
        $getFederationsFromLicensesAction = new GetFederationsFromLicensesAction;
        $this->federations_preview = $getFederationsFromLicensesAction($this->federation_licenses);
    }

    public function getFederationsByCountryId()
    {
        if ($this->recipient == 'federation') {
            $this->federations_preview = Federation::whereIn('country_id', $this->federation_countries)->get();
        }
    }

    public function getEntitiesByFederation($newFederationIds = [])
    {
        if ($this->recipient == 'entity') {
            $newCount = Entity::whereHas('federations', function ($query) use ($newFederationIds) {
                $query->whereIn('federation_id', $newFederationIds);
            })->count();
            // Calculate the difference
            $difference = $newCount - $this->entities_preview_count;
            // Update the entitiesCount
            $this->entities_preview_count += $difference;
        }
    }

    public function getEntitiesFromLicenses()
    {
        $getEntitiesFromLicenses = new GetEntitiesFromLicensesAction;
        $entities = $getEntitiesFromLicenses($this->entities_licenses);
        $this->entities_preview_count = $entities->count();
    }

    public function getIndividualsByFederation()
    {
        $results = Federation::whereIn('id', $this->individuals_federations)->with('individuals')->firstOrFail()->individuals;
        // TODO corrige isto nao pode ser um count assim
        $this->individuals_preview_count = $results->count();
    }

    public function getIndividualsFromLicenses()
    {
        $getIndividualsFromLicenses = new GetIndividualsFromLicensesAction;
        $individuals = $getIndividualsFromLicenses($this->individuals_licenses);
        $this->individuals_preview_count = $individuals->count();
    }

    public function getIndividualsFromCertifications()
    {
        $getIndividualsFromCertifications = new GetIndividualsFromCertificationsAction;
        $individuals = $getIndividualsFromCertifications($this->individuals_certifications);
        $this->individuals_preview_count = $individuals->count();
    }

    public function getIndividualsFromProfessionalRoles()
    {
        $getIndividualsFromProfessionalRoles = new GetIndividualsFromProfessionalRolesAction;
        $individuals = $getIndividualsFromProfessionalRoles($this->individuals_professional_roles);
        $this->individuals_preview_count = $individuals->count();
    }

    public function saveAttachment()
    {
        Log::info('saveAttachment called', ['attachments' => $this->attachments]);

        // Validate the data
        $this->validationErrors = [];

        $validator = Validator::make([
            'attachments' => $this->attachments,
            'recipient' => $this->recipient,
            'selected_category' => $this->selected_category,
            'selected_language' => $this->selected_language,
        ], [
            'attachments' => [
                'required',
                'file',
                'max:' . (1024 * 1024 * 20), // 40MB, matching config
                'mimes:pdf,png,jpg,jpeg,zip,doc,docx,xls,xlsx,ppt,pptx,txt', // Add more file types
            ],
            'recipient' => 'required|string',
            'selected_category' => 'required|int',
            'selected_language' => 'nullable|int',
        ]);

        if ($validator->fails()) {
            $this->validationErrors = $validator->errors()->getMessages();
        }

        DB::beginTransaction();

        try {
            // Create a new attachment
            $attachment = new Attachment;
            $attachment->recipient_name = $this->recipient;
            $attachment->name = $this->attachment_name;

            if (in_array($this->recipient, ['entity', 'individual'])) {
                $attachment->recipient_id = null;
            }

            $attachment->category_id = $this->selected_category;
            $attachment->owner()->associate(GetUserTypeAction::execute(auth()->user()));
            $attachment->committee_id = ! empty($this->committee) ? $this->committee->id : null;
            $attachment->language_id = $this->selected_language === 'all' ? null : $this->selected_language;

            $attachment->save();

            if (in_array($this->recipient, ['all_entities', 'entity']) && $this->entity_licenses) {
                $attachment->licenses()->sync(array_keys($this->entity_licenses->toArray()));
            }
            if (in_array($this->recipient, ['all_individuals', 'individual']) && $this->individual_licenses) {
                $attachment->licenses()->sync(array_keys($this->individual_licenses->toArray()));
            }

            // Sync Certifications
            if (in_array($this->recipient, ['all_individuals', 'individual']) && $this->individuals_certifications) {
                $attachment->certifications()->sync($this->individuals_certifications);
            }

            // Sync Federations
            if (in_array($this->recipient, ['all_entities', 'entity'])) {
                $attachment->filterFederation()->sync(auth()->user()->federations()->first()->id);
            }

            // Sync ProfessionalRoles
            if (in_array($this->recipient, ['all_individuals', 'individual']) && $this->individuals_professional_roles) {
                $attachment->professionalRoles()->sync($this->individuals_professional_roles);
            }

            /*
            foreach ($this->attachments as $attch) {
                $attachment->addMedia($attch)->toMediaCollection('attachments');
            }
            */

            // Handling a single file upload
            if ($this->attachments) {
                $fileType = $this->attachments->getMimeType();
                $fileName = $this->attachments->getClientOriginalName();
                Log::info('Attempting to upload file', ['name' => $fileName, 'type' => $fileType]);

                try {
                    $media = $attachment->addMedia($this->attachments->getRealPath())
                        ->usingName($fileName)
                        ->toMediaCollection('attachments');

                    Log::info('File uploaded successfully', ['name' => $media->file_name, 'id' => $media->id]);
                } catch (\Exception $e) {
                    Log::error('Error uploading file', ['name' => $fileName, 'error' => $e->getMessage()]);
                    throw $e;
                }
            }

            // Commit the transaction
            DB::commit();
            // After successfully saving the attachment and its relationships
            event(new AttachmentFileCreatedEvent($attachment));

            if (! empty($this->committee)) {
                return redirect(route('federation.committee.attachments.index', $this->committee))->with('success', 'File uploaded successfully.');
            } else {
                return redirect(route('federation.attachments.index'))->with('success', 'File uploaded successfully.');
            }

            /*
            // Reset all public properties
            $this->reset();

            //message notification for user ($messages ?)
            $this->message_type = 'success';
            $this->message_title = 'Success';
            $this->message_body = 'File uploaded successfully.';
            */
        } catch (Exception $e) {
            Log::error('Error in saveAttachment', ['error' => $e->getMessage()]);

            // Rollback the transaction in case of errors
            DB::rollback();
            // Notify the user of the error
            $this->message_type = 'error';
            $this->message_title = 'An Error Occurred';
            $this->message_body = 'There was an error saving the attachment: ' . $e->getMessage();
        }
    }
}
