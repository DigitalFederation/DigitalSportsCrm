<?php

namespace App\Livewire;

use App\Traits\StreamsMediaFromStorage;
use Domain\EvtEvents\Models\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use ZipArchive;

class EventAttachmentsTable extends Component
{
    use StreamsMediaFromStorage;
    use WithFileUploads;

    public Event $event;
    public $newAttachment;
    public $newAttachmentName;
    public $confirmingAttachmentDeletion = false; // State to manage the dialog visibility

    public $attachmentIdToRemove; // State to manage which attachment to remove

    public function mount(Event $event)
    {
        $this->event = $event;
    }

    public function getGeneralAttachmentsProperty()
    {
        return Media::where('model_id', $this->event->id)
            ->where('collection_name', 'event-general-attachments')
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function saveAttachment()
    {
        // Custom error messages for validation
        $messages = [
            'newAttachment.required' => 'Please select a file to upload.',
            'newAttachment.file' => 'The uploaded file is not valid.',
            'newAttachment.mimes' => 'Only zip, jpg, jpeg, png, pdf, doc, and docx files are allowed.',
            'newAttachment.max' => 'The file size cannot exceed 80MB.',
        ];

        // Validate the uploaded file
        $this->validate([
            'newAttachment' => 'required|file|mimes:zip,jpg,jpeg,png,pdf,doc,docx|max:80240', // Validation rules
            'newAttachmentName' => 'nullable|string|max:255', // validation rule for the file name
        ], $messages);

        if ($this->newAttachment) {
            try {
                $fileName = $this->newAttachmentName ? $this->newAttachmentName : $this->newAttachment->getClientOriginalName();
                $collectionName = 'event-general-attachments';
                $this->event->addMedia($this->newAttachment->getRealPath())
                    ->usingName($fileName)
                    ->toMediaCollection($collectionName);

                // Clear attachment cache
                Cache::forget("event_attachments_{$this->event->id}");
                Cache::forget("event_attachments_file_{$this->event->id}");

                $this->newAttachment = null; // Reset the file upload input
                $this->newAttachmentName = null; // Reset the file name input
                $this->dispatch('attachmentAdded'); // Optional: Emit a browser event to notify the user
            } catch (FileIsTooBig $e) {
                // Clear specific error for Spatie media library size limit
                $this->addError('newAttachment', 'The file is too large. Maximum allowed size is 80MB.');

                return;
            } catch (\Exception $e) {
                // Check if this is a PHP upload size limit issue
                if (strpos($e->getMessage(), 'upload_max_filesize') !== false) {
                    $this->addError('newAttachment', 'The file exceeds the server\'s upload limit. Please try a smaller file.');
                } else {
                    // Generic error for other exceptions
                    $this->addError('newAttachment', 'Failed to upload file: ' . $e->getMessage());
                }

                return;
            }
        }
    }

    public function confirmDeleteAttachment($attachmentId)
    {
        $this->confirmingAttachmentDeletion = true;
        $this->attachmentIdToRemove = $attachmentId;
    }

    public function deleteAttachment()
    {
        $attachment = Media::find($this->attachmentIdToRemove);
        if ($attachment) {
            $attachment->delete();
            // Clear attachment cache
            Cache::forget("event_attachments_{$this->event->id}");
            Cache::forget("event_attachments_file_{$this->event->id}");
            $this->dispatch('attachmentDeleted'); // Emitting event to refresh parent component, if needed.
        }
        $this->confirmingAttachmentDeletion = false; // Close the dialog
    }

    public function downloadAttachment($attachmentId)
    {
        $attachment = Media::find($attachmentId);
        if ($attachment) {
            $downloadFilename = $this->buildDownloadFilename($attachment);

            return $this->streamMediaDownload($attachment, $downloadFilename);
        }
    }

    public function downloadAllAttachments($collectionName = null)
    {
        // Create a unique temporary zip file name
        $zipFileName = 'event_' . $this->event->id . '_attachments_' . Str::random(8) . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Ensure the temp directory exists
        if (! Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        $tempFiles = [];

        try {
            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                // Get attachments based on collection name, or all if not specified
                $query = Media::where('model_id', $this->event->id);
                if ($collectionName) {
                    $query->where('collection_name', $collectionName);
                } else {
                    $query->where('collection_name', 'event-general-attachments');
                }
                $attachments = $query->get();

                foreach ($attachments as $attachment) {
                    $filename = $this->buildDownloadFilename($attachment);

                    // Get temp path for cloud-compatible file access
                    $tempPath = $this->getMediaTempPath($attachment);
                    if ($tempPath) {
                        $tempFiles[] = $tempPath;
                        $zip->addFile($tempPath, $filename);
                    }
                }

                $zip->close();

                // Return the zip file as a download
                if (file_exists($zipPath)) {
                    return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
                }
            }
        } finally {
            // Clean up temp files
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    @unlink($tempFile);
                }
            }
        }

        return back()->with('error', 'Could not create zip file');
    }

    public function render()
    {
        return view('livewire.event-attachments-table', [
            'generalAttachments' => $this->getGeneralAttachmentsProperty(),
        ]);
    }
}
