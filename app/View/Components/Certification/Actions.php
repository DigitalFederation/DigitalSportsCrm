<?php

namespace App\View\Components\Certification;

use Domain\Certifications\States\ActiveCertificationAttributedState;
use Illuminate\View\Component;

class Actions extends Component
{
    public $certification;
    public $actions;
    public $canActivate;
    public $canSuspend;
    public $canReject;
    public $canEdit;

    public function __construct($certification)
    {
        $this->certification = $certification;
        $this->determineAvailableActions();
    }

    protected function determineAvailableActions(): void
    {
        $this->canActivate = $this->certification->status_class !== ActiveCertificationAttributedState::class
            && ! empty($this->certification->national_code);

        $this->canSuspend = $this->certification->status_class === ActiveCertificationAttributedState::class;

        $this->canReject = ! $this->certification->isActive();

        $this->canEdit = auth()->user()->can('edit', $this->certification);
    }

    public function render()
    {
        return view('components.certification.actions');
    }
}
