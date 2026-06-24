<?php

namespace App\Livewire\Widgets\OfficialDocuments;

use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\PendingOfficialDocumentState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Pending extends Component
{
    public Collection $documents;

    public function render(): View
    {
        try {
            $this->documents = $this->findDocuments();

            return view('livewire.widgets.official-documents.pending');

        } catch (Exception $e) {
            Log::error($e->getMessage());

            return view('livewire.widgets.404')->with('title', 'Pending Official Documents');
        }
    }

    public function findDocuments(): Collection
    {
        if (auth()->user()->group()->firstOrFail()->code == 'INDIVIDUAL') {
            return OfficialDocument::where('individual_id', auth()->user()->individuals()->first()->id)->with('individual', 'media')->get();
        } else {
            return OfficialDocument::where('status_class', PendingOfficialDocumentState::class)->with('individual', 'media')->get();
        }
    }
}
