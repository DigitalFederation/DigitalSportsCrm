<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateIndividualProfileRequest;
use App\Models\Country;
use Domain\Geographic\Models\District;
use Domain\Individuals\Actions\EditIndividualProfileAction;
use Domain\Individuals\Models\Individual;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Log;

class ProfileController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(): View
    {
        $individual = $this->authenticatedIndividual();

        return view('web.individual.profile.show', compact('individual'));
    }

    public function edit()
    {
        $individual = $this->authenticatedIndividual();

        $countries = Country::orderBy('name')->get();
        $districts = District::orderBy('name')->get();

        return view('web.individual.profile.edit', compact('individual', 'countries', 'districts'));
    }

    public function update(UpdateIndividualProfileRequest $request, EditIndividualProfileAction $editProfile)
    {
        try {
            DB::beginTransaction();

            $individual = $this->authenticatedIndividual();

            $individual = $editProfile($individual, $request->validated());

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());

            return back()->withInput()->withErrors(__('individual.error_saving_data'));
        }

        return redirect(route('individual.individual.show'))->with('success', __('individual.profile_updated_successfully'));
    }

    private function authenticatedIndividual(): Individual
    {
        return Individual::whereHas('user', function (Builder $query) {
            $query->where('id', auth()->user()->id);
        })->firstOrFail();
    }
}
