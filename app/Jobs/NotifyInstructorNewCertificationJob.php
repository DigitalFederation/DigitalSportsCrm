<?php

namespace App\Jobs;

use App\Notifications\InstructorNewCertificationNotification;
use Domain\Certifications\Models\CertificationAttributed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class NotifyInstructorNewCertificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected CertificationAttributed $certification;

    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(CertificationAttributed $certification)
    {
        $this->certification = $certification;

        $mainInstructor = $certification->mainInstructor()->first();

        if ($mainInstructor) {
            $this->user = $mainInstructor->user()->first();
        }

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->user) {
            Notification::send($this->user, new InstructorNewCertificationNotification($this->certification));
        }

    }
}
