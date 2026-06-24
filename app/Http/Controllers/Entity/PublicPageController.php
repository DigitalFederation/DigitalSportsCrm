<?php

declare(strict_types=1);

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole(['entity-admin', 'entity-diving-services'])) {
            abort(403, 'Unauthorized action.');
        }

        $entity = $user->entities()->first();

        if (! $entity) {
            abort(404, 'Entity not found for the current user.');
        }

        return view('web.entity.public_page.index', compact('entity'));
    }
}
