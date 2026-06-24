<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\DisciplineTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BulkDuplicateDisciplinesAction
{
    protected $duplicateDisciplineAction;

    public function __construct(DuplicateDisciplineAction $duplicateDisciplineAction)
    {
        $this->duplicateDisciplineAction = $duplicateDisciplineAction;
    }

    /**
     * Execute the action to duplicate multiple disciplines.
     *
     * @param  Collection|array  $disciplines  The disciplines to duplicate
     * @param  callable|null  $callback  Optional callback function to be called after each duplication
     * @return Collection The newly created disciplines
     *
     * @throws \Exception If duplication fails
     */
    public function execute($disciplines, ?callable $callback = null): Collection
    {
        $disciplines = $this->resolveDisciplines($disciplines);
        $newDisciplines = collect();

        DB::beginTransaction();
        try {
            foreach ($disciplines as $discipline) {
                $newDiscipline = $this->duplicateDisciplineAction->execute($discipline);
                $newDisciplines->push($newDiscipline);

                if ($callback) {
                    $callback($discipline, $newDiscipline);
                }
            }
            DB::commit();

            return $newDisciplines;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Execute the action to duplicate disciplines from a template.
     *
     * @param  int|DisciplineTemplate  $template  The template containing disciplines to duplicate
     * @param  callable|null  $callback  Optional callback function to be called after each duplication
     * @return Collection The newly created disciplines
     *
     * @throws \Exception If duplication fails
     */
    public function executeFromTemplate($template, ?callable $callback = null): Collection
    {
        $template = $this->resolveTemplate($template);

        return $this->execute($template->disciplines, $callback);
    }

    /**
     * Resolve the disciplines into a collection of Discipline models.
     *
     * @param  Collection|array  $disciplines
     */
    protected function resolveDisciplines($disciplines): Collection
    {
        if (is_array($disciplines)) {
            if (empty($disciplines)) {
                return collect();
            }

            // Check if the array contains IDs or Discipline objects
            if (is_numeric($disciplines[0]) || is_string($disciplines[0])) {
                return Discipline::whereIn('id', $disciplines)->get();
            }

            return collect($disciplines);
        }

        return $disciplines instanceof Collection ? $disciplines : collect([$disciplines]);
    }

    /**
     * Resolve the template into a DisciplineTemplate model.
     *
     * @param  int|DisciplineTemplate  $template
     */
    protected function resolveTemplate($template): DisciplineTemplate
    {
        if ($template instanceof DisciplineTemplate) {
            return $template;
        }

        return DisciplineTemplate::with('disciplines')->findOrFail($template);
    }
}
