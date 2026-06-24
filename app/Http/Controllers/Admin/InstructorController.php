<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\EntityProfessionalRole;
use Illuminate\Contracts\View\View;

class InstructorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $instructors = EntityProfessionalRole::with('entity', 'instructor')->get();

        return view('admin.instructors.index', compact('instructors'));
    }
}
