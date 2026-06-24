<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Session;
use Illuminate\View\Component;

class ImpersonationBar extends Component
{
    public $isImpersonating;
    public $impersonatedUser;

    public function __construct()
    {

        $this->isImpersonating = Session::has('impersonate');
        if ($this->isImpersonating) {
            $this->impersonatedUser = \App\Models\User::find(Session::get('impersonate'));
        }
    }

    public function render()
    {

        return view('components.impersonation-bar');
    }
}
