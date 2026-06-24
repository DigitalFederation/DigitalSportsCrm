<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FederationVotingRightsExport;
use App\Http\Controllers\Controller;
use Domain\Federations\Models\Federation;
use Domain\Federations\Models\FederationVotingRight;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FederationVotingRightController extends Controller
{
    public function index(): View
    {
        // The view will now contain the Livewire component which handles data loading
        return view('web.admin.federation_voting_right.index');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $year = $request->input('year', Carbon::now()->year);

        // Fetch data similar to the Livewire component
        $federations = Federation::where('is_local', false)
            ->with(['votingRights' => function ($query) use ($year) {
                $query->where('year', $year);
            }])
            ->orderBy('member_code')
            ->get()
            ->each(function ($federation) use ($year) {
                // Ensure a placeholder votingRights relation exists if none found for the year
                if ($federation->votingRights->isEmpty()) {
                    $defaultVotingRight = new FederationVotingRight(['year' => $year]);
                    $federation->setRelation('votingRights', collect([$defaultVotingRight]));
                }
            });

        $filename = "federation-voting-rights-{$year}.xlsx";

        return Excel::download(new FederationVotingRightsExport($federations, $year), $filename);
    }
}
