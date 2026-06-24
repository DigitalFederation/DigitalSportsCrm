<?php

namespace Domain\Individuals\Actions;

use Domain\Individuals\Models\Individual;
use Exception;
use Illuminate\Support\Facades\DB;

class DeleteIndividualAction
{
    /**
     * @throws Exception
     */
    public function __invoke(string $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $individual = Individual::findOrFail($id);

                // Perform the soft delete
                $individual->delete();

                // If there's an associated user, remove the association
                if ($individual->user) {
                    $individual->user()->dissociate();
                    $individual->save();
                }
            });

            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to delete individual: ' . $e->getMessage());
        }
    }
}
