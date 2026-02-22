<?php

namespace App\Livewire\Admin;

use App\Models\Party;
use Livewire\Component;
use Livewire\WithPagination;

class PartyIndex extends Component
{
    use WithPagination;

    public string $status = '';
    public string $q = '';

    protected $queryString = [
        'status' => ['except' => ''],
        'q' => ['except' => ''],
    ];

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->status = '';
        $this->q = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Party::query()
            ->with(['createdByAdmin', 'closedByAdmin'])
            ->orderByDesc('created_at');

        if ($this->status !== '' && in_array($this->status, ['draft', 'active', 'closed'], true)) {
            $query->where('status', $this->status);
        }

        $search = trim($this->q);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $parties = $query->paginate(20);
        $hasActiveParty = Party::where('status', 'active')->exists();

        return view('livewire.admin.party-index', [
            'parties' => $parties,
            'hasActiveParty' => $hasActiveParty,
        ]);
    }
}
