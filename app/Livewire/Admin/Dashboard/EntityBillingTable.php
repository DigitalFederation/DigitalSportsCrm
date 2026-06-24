<?php

namespace App\Livewire\Admin\Dashboard;

use App\Livewire\Concerns\HasEntityBillingQuery;
use Domain\Federations\Models\Federation;
use Livewire\Component;
use Livewire\WithPagination;

class EntityBillingTable extends Component
{
    use HasEntityBillingQuery;
    use WithPagination;

    public function render()
    {
        $federation = Federation::where('is_default_federation', true)->first();

        $entities = $this->getTopBilledEntities($federation->id ?? null);

        return view('livewire.federation.dashboard.entity-billing-table', [
            'entities' => $entities,
        ]);
    }
}
