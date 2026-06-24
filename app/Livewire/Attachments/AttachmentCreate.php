<?php

namespace App\Livewire\Attachments;

use Domain\Attachments\Models\Attachment;
use Domain\Entities\Actions\GetEntitiesFromLicensesAction;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\GetIndividualsFromCertificationsAction;
use Domain\Individuals\Actions\GetIndividualsFromProfessionalRolesAction;
use Domain\Users\Actions\GetUserTypeAction;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class AttachmentCreate extends Component
{
    use WithFileUploads;

    public $attachments = [];

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

    public $federations;

    public $recipient;

    public $recipients = [
        'all' => 'All',
        'all_federations' => 'All Federations',
        'all_entities' => 'All Entities',
        'all_individuals' => 'All Individuals',
        'all_entities_&_individuals' => 'All Entities & Individuals',
        'individual' => 'Filter Individuals',
        'entity' => 'Filter Entities',
        'federation' => 'Filter Federations',
    ];

    public $federations_preview = [];

    public $federation_countries = [];

    public $validationErrors = [];

    public $federation_federations = [];

    public $certifications = [];

    public $entities_federations = [];

    public $entities_licenses = [];

    public $entities_preview_count = 0;

    public $individual_licenses;

    public $individuals_federations;

    public $individuals_certifications;

    public $individuals_professional_roles;

    public $individuals_preview_count = 0;

    public function render()
    {
        return view('livewire.attachments.attachment-create', ['committee' => $this->committee]);
    }

    // Computed properties
    public function getDisableEntitiesFederationsProperty()
    {
        return count($this->entities_licenses) > 0;
    }

    public function getDisableEntitiesLicensesProperty()
    {
        return count($this->entities_federations) > 0;
    }

    public function getDisableFederationFederationsProperty()
    {
        return count($this->federation_countries) > 0;
    }

    public function getDisableFederationCountriesProperty()
    {
        return count($this->federation_federations) > 0;
    }

    /**
     * As documented on:
     * https://livewire.laravel.com/docs/events#listening-for-events
     */

    /**
     * Federations Listeners
     */
    #[On('selectedMultipleUpdatedValue.federation_countries')]
    public function updateFederationCountries($values)
    {
        $this->federation_countries = $values;
        $this->getFederationsByCountryId();

        // Disable the Federation Countries selection
        if ($this->disableFederationFederations) {
            $this->federation_federations = [];
        }
    }

    #[On('selectedMultipleUpdatedValue.federation_federations')]
    public function updateFederationFederations($values)
    {
        $this->federation_federations = $values;

        // Disable the Federation Countries selection
        if ($this->disableFederationCountries) {
            $this->federation_countries = [];
        }
    }

    /**
     * Entities Listeners
     */
    #[On('selectedMultipleUpdatedValue.entities_federations')]
    public function updateEntitiesFederations($values)
    {
        $this->entities_federations = $values;
        $this->getEntitiesByFederation($values);

        // Disable the Entity Licenses selection
        if ($this->disableEntitiesLicenses) {
            $this->entities_licenses = [];
        }
    }

    #[On('selectedMultipleUpdatedValue.entities_licenses')]
    public function updateEntitiesLicenses($values)
    {
        $this->entities_licenses = $values;
        $this->getEntitiesFromLicenses($values);

        // Disable the Entity Federations selection
        if ($this->disableEntitiesFederations) {
            $this->entities_federations = [];
        }
    }

    /**
     * Individuals Listeners
     */
    #[On('selectedMultipleUpdatedValue.individuals_federations')]
    public function updateIndividualFederations($values)
    {
        $this->individuals_federations = $values;
        $this->getIndividualsByFederation($values);
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

    public function getIndividualsFromCertifications()
    {
        $getIndividualsFromCertifications = new GetIndividualsFromCertificationsAction;
        $individuals = $getIndividualsFromCertifications($this->individuals_certifications);
        $this->individuals_preview_count = $individuals->count();
    }
    public function getIndividualsByFederation()
    {
        $results = Federation::whereIn('id', $this->individuals_federations)->with('individuals')->firstOrFail()->individuals;
        // TODO corrige isto nao pode ser um count assim
        $this->individuals_preview_count = $results->count();
    }

    public function getIndividualsFromProfessionalRoles()
    {
        $getIndividualsFromProfessionalRoles = new GetIndividualsFromProfessionalRolesAction;
        $individuals = $getIndividualsFromProfessionalRoles($this->individuals_professional_roles);
        $this->individuals_preview_count = $individuals->count();
    }

    public function saveAttachment()
    {

        // Validate the data
        $this->validationErrors = [];
        $validator = Validator::make([
            'attachments' => $this->attachments,
            'recipient' => $this->recipient,
            'selected_category' => $this->selected_category,
            'selected_language' => $this->selected_language,
        ], [
            'attachments' => 'required|file|mimes:png,jpg,pdf,docx,doc,xls,xlsx|max:20480',  // Max size 20MB
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
            $attachment->category_id = $this->selected_category;
            $attachment->owner()->associate(GetUserTypeAction::execute(auth()->user()));
            $attachment->committee_id = ! empty($this->committee) ? $this->committee->id : null;
            $attachment->language_id = ! empty($this->selected_language) ? $this->selected_language : null;

            // Sync Federations
            if (in_array($this->recipient, ['federation'])) {
                $attachment->recipient_type = Federation::class;
                // $attachment->recipient_id = $this->federation_federations[0];
            }

            $attachment->save();

            // Sync Federations for 'federation' recipient type
            if (in_array($this->recipient, ['federation'])) {
                // Sync with selected federations
                $attachment->filterFederation()->sync($this->federation_federations);
            }

            // Sync Licenses
            if (in_array($this->recipient, ['all_entities', 'entity']) && ! empty($this->entities_licenses)) {
                $attachment->licenses()->sync(array_keys($this->entities_licenses->toArray()));
            } else {
                $attachment->licenses()->detach();  // Important to detach if no licenses should be associated
            }

            // Sync Countries
            if (in_array($this->recipient, ['all_federations', 'federation']) && $this->federation_countries) {
                $attachment->countries()->sync($this->federation_countries);
            } else {
                $attachment->countries()->detach();  // Important to detach if no countries should be associated
            }

            // Sync Entities
            if (in_array($this->recipient, ['all_entities', 'entity']) && $this->entities_federations) {
                $attachment->filterFederation()->sync($this->entities_federations);
            }

            // Sync ProfessionalRoles
            if (in_array($this->recipient, ['all_individuals', 'individual']) && ! empty($this->individuals_professional_roles)) {
                $attachment->professionalRoles()->sync($this->individuals_professional_roles);
            } else {
                $attachment->professionalRoles()->detach();  // Important to detach if no roles should be associated
            }

            /*
            foreach ($this->attachments as $attch) {
                $attachment->addMedia($attch)->toMediaCollection('attachments');
            }
            */

            // Handling a single file upload
            if ($this->attachments) {
                foreach ($this->attachments as $files) {
                    $attachment->addMedia($files->getRealPath())
                        ->usingName($files->getClientOriginalName())
                        ->toMediaCollection('attachments');
                }
            } else {
                Log::error('No files uploaded: '.json_encode($this->attachments));
            }

            // Commit the transaction
            DB::commit();

            if (! empty($this->committee)) {
                return redirect(route('admin.committee.attachments.index', $this->committee))->with('success', __('File uploaded successfully.'));
            } else {
                return redirect(route('admin.attachments.index'))->with('success', __('File uploaded successfully.'));
            }
        } catch (Exception $e) {
            // Rollback the transaction in case of errors
            DB::rollback();
            // Notify the user of the error
            $this->message_type = 'error';
            $this->message_title = 'An Error Occurred';
            $this->message_body = 'There was an error saving the attachment: '.$e->getMessage();
        }
    }
}
