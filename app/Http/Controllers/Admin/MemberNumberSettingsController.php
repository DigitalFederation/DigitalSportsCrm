<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Memberships\Services\MemberNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberNumberSettingsController extends Controller
{
    private MemberNumberService $memberNumberService;

    public function __construct(MemberNumberService $memberNumberService)
    {
        $this->memberNumberService = $memberNumberService;
    }

    public function index(): View
    {
        $individualCounter = $this->memberNumberService->getCurrentIndividualCounter();
        $entityCounter = $this->memberNumberService->getCurrentEntityCounter();

        return view('web.admin.member-number-settings.index', compact('individualCounter', 'entityCounter'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'individual_counter' => 'required|integer|min:1',
            'entity_counter' => 'required|integer|min:1',
        ]);

        try {
            $this->memberNumberService->updateIndividualCounter($request->individual_counter);
            $this->memberNumberService->updateEntityCounter($request->entity_counter);

            return redirect()->route('admin.member-number-settings.index')
                ->with('success', 'Member number counters updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.member-number-settings.index')
                ->with('error', 'Failed to update member number counters: ' . $e->getMessage());
        }
    }
}
