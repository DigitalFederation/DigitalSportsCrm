<?php

namespace App\Livewire\Federation;

use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BoardMembersForm extends Component
{
    public ?Federation $federation = null;

    public ?string $boardPresidentId = '';

    public ?string $assemblyPresidentId = '';

    public ?Individual $boardPresident = null;

    public ?Individual $assemblyPresident = null;

    public string $boardPresidentSearch = '';

    public string $assemblyPresidentSearch = '';

    public ?string $boardPresidentError = null;

    public ?string $assemblyPresidentError = null;

    public bool $boardPresidentSuccess = false;

    public bool $assemblyPresidentSuccess = false;

    public function mount(): void
    {
        $this->federation = Auth::user()->federations()->first();

        if ($this->federation) {
            $boardMembers = $this->federation->board_members ?? [];

            if (isset($boardMembers['board_president_id'])) {
                $this->boardPresidentId = $boardMembers['board_president_id'];
                $this->boardPresident = Individual::find($this->boardPresidentId);
            }

            if (isset($boardMembers['assembly_president_id'])) {
                $this->assemblyPresidentId = $boardMembers['assembly_president_id'];
                $this->assemblyPresident = Individual::find($this->assemblyPresidentId);
            }
        }
    }

    public function searchBoardPresident(): void
    {
        $this->boardPresidentError = null;
        $this->boardPresidentSuccess = false;

        if (empty($this->boardPresidentSearch)) {
            $this->boardPresidentError = __('federation.enter_individual_id');

            return;
        }

        $individual = Individual::where('member_number', $this->boardPresidentSearch)->first();

        if (! $individual) {
            $this->boardPresidentError = __('federation.individual_not_found');

            return;
        }

        $this->boardPresident = $individual;
        $this->boardPresidentId = $individual->id;
        $this->boardPresidentSearch = '';
        $this->saveBoardMembers();
        $this->boardPresidentSuccess = true;
    }

    public function searchAssemblyPresident(): void
    {
        $this->assemblyPresidentError = null;
        $this->assemblyPresidentSuccess = false;

        if (empty($this->assemblyPresidentSearch)) {
            $this->assemblyPresidentError = __('federation.enter_individual_id');

            return;
        }

        $individual = Individual::where('member_number', $this->assemblyPresidentSearch)->first();

        if (! $individual) {
            $this->assemblyPresidentError = __('federation.individual_not_found');

            return;
        }

        $this->assemblyPresident = $individual;
        $this->assemblyPresidentId = $individual->id;
        $this->assemblyPresidentSearch = '';
        $this->saveBoardMembers();
        $this->assemblyPresidentSuccess = true;
    }

    public function removeBoardPresident(): void
    {
        $this->boardPresident = null;
        $this->boardPresidentId = '';
        $this->boardPresidentSuccess = false;
        $this->saveBoardMembers();
    }

    public function removeAssemblyPresident(): void
    {
        $this->assemblyPresident = null;
        $this->assemblyPresidentId = '';
        $this->assemblyPresidentSuccess = false;
        $this->saveBoardMembers();
    }

    private function saveBoardMembers(): void
    {
        if (! $this->federation) {
            return;
        }

        $user = Auth::user();
        if (! $user || ! $user->federations()->where('federation.id', $this->federation->id)->exists()) {
            abort(403);
        }

        $boardMembers = $this->federation->board_members ?? [];

        if ($this->boardPresidentId) {
            $boardMembers['board_president_id'] = $this->boardPresidentId;
        } else {
            unset($boardMembers['board_president_id']);
        }

        if ($this->assemblyPresidentId) {
            $boardMembers['assembly_president_id'] = $this->assemblyPresidentId;
        } else {
            unset($boardMembers['assembly_president_id']);
        }

        $this->federation->board_members = $boardMembers;
        $this->federation->save();
    }

    public function render()
    {
        return view('livewire.federation.board-members-form');
    }
}
