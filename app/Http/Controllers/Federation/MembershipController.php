<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use Domain\Memberships\Models\Membership;
use Illuminate\Contracts\View\View;

class MembershipController extends Controller
{
    public function index(): View
    {
        $memberships = Membership::with('plans', 'parentPlans')->where('federation_id', auth()->user()->federations()->value('federation.id'))->orderBy('current_term_ends_at', 'desc')->paginate();

        return view('web.federation.membership.index', compact('memberships'));
    }
}
