<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicSearchCertificationRequest;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\RejectedCertificationAttributedState;
use Domain\Individuals\Models\Individual;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificationController extends Controller
{
    public function index(): View
    {
        return view('web.public.certification.index');
    }

    public function search(PublicSearchCertificationRequest $request): View
    {
        $getCertifications = CertificationAttributed::select('id', 'certification_name', 'certification_id', 'federation_id', 'created_at', 'status_class', 'current_term_starts_at', 'national_code')
            ->whereHas('individual', function (Builder $query) use ($request) {
                $validated = $request->validated();
                $query->select('id', 'name', 'surname', 'birthdate', 'member_code')
                    ->when(! empty($validated['name']) && ! empty($validated['surname']) && ! empty($validated['birthdate']), function (Builder $query) use ($validated) {
                        $query->where('name', 'like', $validated['name'])
                            ->where('surname', 'like', $validated['surname'])
                            ->whereDate('birthdate', $validated['birthdate']);
                    })->when(! empty($validated['member_code']), function (Builder $query) use ($validated) {
                        $query->where('member_code', $validated['member_code']);
                    });
            })
            ->where('status_class', '!=', RejectedCertificationAttributedState::class)
            ->with(['certification' => function (BelongsTo $query) {
                $query->select('id', 'committee_id', 'certification_view')->with('committee:id,code');
            }, 'federation.country'])->orderBy('created_at', 'desc')->get();

        $certificationsUnique = $getCertifications->unique('certification_id');

        $certificationsByCommittee = [
            'diving' => $certificationsUnique->filter(function ($certification) {
                return $certification->certification->committee->code === 'DIVING';
            }),
            'scientific' => $certificationsUnique->filter(function ($certification) {
                return $certification->certification->committee->code === 'SCIENTIFIC';
            }),
            'sport' => $certificationsUnique->filter(function ($certification) {
                return $certification->certification->committee->code === 'SPORT';
            }),
        ];

        // Get Individual from the request member_code
        $individual = null;
        $validatedData = $request->validated(); // Get validated data once

        if (! empty($validatedData['member_code'])) {
            $individual = Individual::where('member_code', $validatedData['member_code'])->first();
        } elseif (! empty($validatedData['name']) && ! empty($validatedData['surname']) && ! empty($validatedData['birthdate'])) {
            $individual = Individual::where('name', 'like', $validatedData['name'])
                ->where('surname', 'like', $validatedData['surname'])
                ->whereDate('birthdate', $validatedData['birthdate'])->first();
        }

        // TODO: Is this faster than the previous one? It doesnt seem to be
        /*
        $certificationsByCommittee = CertificationAttributed::query()
            ->when($request->filled('member_code'), fn($q) =>
            $q->whereHas('individual', fn($q) => $q->where('member_code', $request->get('member_code'))))
            ->when($request->filled(['name', 'surname', 'birthdate']), fn($q) =>
            $q->whereHas('individual', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->get('name')}%")
                    ->where('surname', 'like', "%{$request->get('surname')}%")
                    ->whereDate('birthdate', $request->get('birthdate'));
            }))
            ->where('status_class', '!=', RejectedCertificationAttributedState::class)
            ->with(['certification.committee:id,code', 'federation.country'])
            ->latest()
            ->distinct('certification_id')    // or groupBy
            ->get()
            ->groupBy(fn($c) => strtolower($c->certification->committee->code));
        */

        return view('web.public.certification.search', compact('certificationsByCommittee', 'individual'));
    }
}
