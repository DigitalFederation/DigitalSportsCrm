<?php

namespace App\View\Components\Certification;

use Illuminate\View\Component;

class InstructorInfo extends Component
{
    public $mainInstructor;
    public $assistants;
    public $showAssistants;
    public $mainInstructorLicenses;

    public $instructorLevel;

    public function __construct($mainInstructor = null, $assistants = null)
    {
        $this->mainInstructor = $mainInstructor;
        $this->assistants = $assistants;
        $this->showAssistants = ! empty($assistants) && $assistants->count() > 0;

        if ($this->mainInstructor) {

            $this->mainInstructorLicenses = $this->mainInstructor->licenses()
                ->with(['license.professionalRole'])
                ->get();
            if ($this->mainInstructorLicenses) {
                $this->instructorLevel = $this->getInstructorLevel();
            }
        }
    }

    public function getInstructorLevel(): string
    {
        if (! $this->mainInstructor) {
            return '';
        }

        return $this->mainInstructorLicenses
            ->whereIn('license.professional_role.role', ['INSTRUCTOR', 'LEADER'])
            ->sortByDesc('created_at')
            ->first()?->license->name ?? '';
    }

    public function render()
    {
        return view('components.certification.instructor-info');
    }
}
