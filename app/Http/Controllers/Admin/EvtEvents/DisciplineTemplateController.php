<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\Actions\CreateDisciplineTemplateAction;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\DisciplineTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisciplineTemplateController extends Controller
{
    public function index()
    {
        $templates = DisciplineTemplate::with('disciplines')->get(); // Adjust the namespace if needed
        $disciplines = Discipline::all(); // Get all disciplines for the form

        return view('web.admin.evt_events.discipline_templates.index', compact('templates', 'disciplines'));
    }

    /**
     * Show the form for editing the specified discipline template.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(DisciplineTemplate $disciplineTemplate)
    {
        $disciplines = Discipline::all();

        return view('web.admin.evt_events.discipline_templates.edit', compact('disciplineTemplate', 'disciplines'));
    }

    /**
     * Show the form for creating a new discipline template.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $disciplines = Discipline::all();

        return view('web.admin.evt_events.discipline_templates.create', compact('disciplines'));
    }

    /**
     * Store a newly created discipline template in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, CreateDisciplineTemplateAction $createDisciplineTemplateAction)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'disciplines' => 'required|array',
            'disciplines.*' => 'exists:evt_disciplines,id',
        ]);

        $createDisciplineTemplateAction->execute(
            $validatedData['name'],
            $validatedData['description'] ?? '',
            $validatedData['disciplines']
        );

        return redirect()->route('admin.evt-events.discipline-templates.index')->with('success', 'Discipline template created successfully');
    }

    /**
     * Remove the specified discipline template from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(DisciplineTemplate $disciplineTemplate)
    {
        try {
            // Begin a database transaction
            \DB::beginTransaction();

            // Detach all disciplines from the template
            $disciplineTemplate->disciplines()->detach();

            // Delete the template
            $disciplineTemplate->delete();

            // Commit the transaction
            \DB::commit();

            return redirect()->route('admin.evt-events.discipline-templates.index')
                ->with('success', 'Discipline template deleted successfully');
        } catch (\Exception $e) {
            // If an exception occurs, rollback the transaction
            \DB::rollBack();

            return redirect()->route('admin.evt-events.discipline-templates.index')
                ->with('error', 'An error occurred while deleting the discipline template: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified discipline template in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, DisciplineTemplate $disciplineTemplate)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'disciplines' => 'required|array',
            'disciplines.*' => 'exists:evt_disciplines,id',
        ]);

        DB::beginTransaction();

        try {
            // Update the template details
            $disciplineTemplate->update([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'] ?? '',
            ]);

            // Sync the associated disciplines
            $disciplineTemplate->disciplines()->sync($validatedData['disciplines']);

            DB::commit();

            return redirect()
                ->route('admin.evt-events.discipline-templates.index')
                ->with('success', 'Discipline template updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('admin.evt-events.discipline-templates.edit', $disciplineTemplate)
                ->with('error', 'An error occurred while updating the discipline template: ' . $e->getMessage());
        }
    }
}
