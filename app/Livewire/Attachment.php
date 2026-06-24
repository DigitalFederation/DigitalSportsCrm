<?php

namespace App\Livewire;

use Domain\Attachments\Actions\CreateDivingAttachmentAction;
use Domain\Attachments\DataTransferObject\DivingAttachmentData;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;

class Attachment extends Component
{
    use WithFileUploads;

    public $attachments = [];

    public $model;

    public $individual;

    public $message;

    public $files; // Already existing files

    public $types; // All types

    public $type = ''; // Selected type

    public $official_documents;

    public string $role;

    public array $validationErrors = [];

    public Collection $countries;

    public $country_id = '';

    public Collection $licenses;

    public Collection $federations;

    public array $license_ids = [];

    public Collection $certifications;

    public array $certification_ids = [];

    public $federation_id = null;

    public Collection $professionalRoles;

    public $professionalRole_id = null;

    /**
     * @return RedirectResponse|void
     */
    public function save()
    {
        $this->validationErrors = [];
        $validator = Validator::make([
            'attachments' => $this->attachments,
            'type' => $this->type,
            'license_ids' => $this->license_ids,
            'country_id' => $this->country_id,
            'certification_ids' => $this->certification_ids,
            'professionalRole_id' => $this->professionalRole_id,
        ], [
            'attachments' => 'required|max:1024',
            'type' => 'required',
            'license_ids' => 'array',
            'country_id' => 'required|integer',
            'certification_ids' => 'array',
        ]);

        if ($validator->fails()) {
            $this->validationErrors = $validator->errors()->getMessages();
        } else {
            // Create SportAttachment
            $divingAttachmentData = DivingAttachmentData::fromArray([
                'country_id' => $this->country_id,
                'federation_id' => $this->federation_id ?? null,
                'category' => $this->type,
            ]);

            try {
                DB::beginTransaction();
                $createAction = new CreateDivingAttachmentAction;
                $odModel = $createAction($divingAttachmentData, $this->license_ids, $this->certification_ids);

                foreach ($this->attachments as $attachment) {
                    $odModel->addMedia($attachment)->toMediaCollection('divingAttachment');
                    $this->message = 'File(s) uploaded successfully!';
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
                $this->message = 'Error uploading file(s)!';
            }

            return redirect(request()->header('Referer'));
        }
    }

    public function render()
    {
        return view('livewire.attachment.diving');
    }
}
