<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Diving\Models\DivingEntityTechnicalDirector;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Individuals\Models\Individual;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DivingProfessionalsController extends Controller
{
    /**
     * Display a listing of diving professionals.
     */
    public function index(Request $request): View
    {
        $activeTab = $request->get('tab', 'professionals');
        $filters = $request->input('filter', []);

        // Base query for diving professionals
        $professionalsBaseQuery = EntityProfessionalRole::query()
            ->whereHas('professionalRole', function ($query) {
                $query->where('committee_id', function ($q) {
                    $q->select('id')->from('committee')->where('code', 'DIVING');
                });
            })
            ->where('status_class', \Domain\Entities\States\ActiveEntityProfessionalRoleState::class);

        // Entities dropdown: only entities that have diving professionals
        $entities = Entity::query()
            ->whereIn('id', (clone $professionalsBaseQuery)->select('entity_id'))
            ->orderBy('name')
            ->pluck('name', 'id');

        // Get diving professionals associated with entities
        $professionals = (clone $professionalsBaseQuery)
            ->with(['individual', 'entity', 'professionalRole'])
            ->when($filters['name'] ?? null, function ($query, $name) {
                $query->whereHas('individual', function ($iq) use ($name) {
                    $iq->where('name', 'like', "%{$name}%")
                        ->orWhere('surname', 'like', "%{$name}%");
                });
            })
            ->when($filters['member_number'] ?? null, function ($query, $memberNumber) {
                $query->whereHas('individual', function ($iq) use ($memberNumber) {
                    $iq->where('member_number', 'like', "%{$memberNumber}%");
                });
            })
            ->when($filters['entity_id'] ?? null, function ($query, $entityId) {
                $query->where('entity_id', $entityId);
            })
            ->orderBy('individual_name')
            ->paginate(20, ['*'], 'professionals_page')
            ->withQueryString();

        // Get technical directors associated with licenses and entities
        $technicalDirectors = DivingEntityTechnicalDirector::query()
            ->with(['individual', 'entity', 'licenseAttributed.license.type'])
            ->where('status_class', \Domain\Diving\States\AssignedDivingTechnicalDirectorState::class)
            ->when($filters['name'] ?? null, function ($query, $name) {
                $query->whereHas('individual', function ($iq) use ($name) {
                    $iq->where('name', 'like', "%{$name}%")
                        ->orWhere('surname', 'like', "%{$name}%");
                });
            })
            ->when($filters['entity_id'] ?? null, function ($query, $entityId) {
                $query->where('entity_id', $entityId);
            })
            ->orderByRaw('(SELECT CONCAT(surname, " ", name) FROM individual WHERE individual.id = diving_entity_technical_directors.individual_id)')
            ->paginate(20, ['*'], 'directors_page')
            ->withQueryString();

        return view('web.admin.diving_professionals.index', compact('professionals', 'technicalDirectors', 'activeTab', 'entities'));
    }

    /**
     * Display the specified diving professional.
     */
    public function show($diving_professional)
    {
        // Find the individual by ID
        $individual = Individual::findOrFail($diving_professional);

        // Redirect to the common individual show view with diving context
        return redirect()->route('admin.individual.show', ['individual' => $individual->id]);
    }
}
