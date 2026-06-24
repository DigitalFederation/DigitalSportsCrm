<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class FileUpload extends Component
{
    use WithFileUploads;

    public $attachments = [];

    public $model;

    public $message;

    public $files; // Already existing files

    public function save()
    {
        $this->validate([
            'attachments' => 'max:1024',
        ]);

        foreach ($this->attachments as $attachment) {
            $this->model->addMedia($attachment)->toMediaCollection('media');
            $this->message = 'File(s) uploaded successfully!';

        }

        $this->attachments = [];
        $this->files = $this->model->getMedia('media');

        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.file-upload');
    }
}
