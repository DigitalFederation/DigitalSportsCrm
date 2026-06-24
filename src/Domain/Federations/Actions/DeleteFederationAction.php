<?php

namespace Domain\Federations\Actions;

use Domain\Federations\Models\Federation;
use Exception;

class DeleteFederationAction
{
    /**
     * @throws Exception
     */
    public function __invoke(int $id): bool
    {
        $federation = Federation::findOrFail($id);

        $dependencyList = $this->checkDependencies($federation);
        if (! empty($dependencyList)) {
            throw new Exception(__('This federation cannot be deleted because it is referenced in other records: ') . implode(', ', $dependencyList), 801);
        }

        $federation->users()->detach();

        return $federation->delete();

    }

    private function checkDependencies(Federation $federation): array
    {
        $dependencies = [];

        if ($federation->childs()->exists()) {
            $dependencies[] = 'child national federations';
        }
        if ($federation->certificationsAttributed()->exists()) {
            $dependencies[] = 'certifications attributed';
        }
        if ($federation->organizers()->exists()) {
            $dependencies[] = 'event organizer';
        }
        if ($federation->individualFederations()->exists()) {
            $dependencies[] = 'individuals';
        }
        if ($federation->licensesAttributed()->exists()) {
            $dependencies[] = 'licenses attributed';
        }
        if ($federation->memberships()->exists()) {
            $dependencies[] = 'memberships';
        }
        if ($federation->entities()->exists()) {
            $dependencies[] = 'associated entities';
        }

        return $dependencies;
    }
}
