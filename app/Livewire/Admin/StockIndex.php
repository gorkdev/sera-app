<?php

namespace App\Livewire\Admin;

use App\Models\Party;
use App\Models\PartyStock;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class StockIndex extends Component
{
    use WithPagination;

    public ?int $partyId = null;
    public string $q = '';

    protected $queryString = [
        'partyId' => ['except' => ''],
        'q' => ['except' => ''],
    ];

    public function mount(?int $partyId = null): void
    {
        $this->partyId = $partyId;
    }

    public function updatingPartyId(): void
    {
        $this->resetPage();
    }

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->partyId = null;
        $this->q = '';
        $this->resetPage();
    }

    public function render()
    {
        $parties = Party::orderByDesc('created_at')->get();
        
        $query = PartyStock::query()
            ->with(['party', 'product.category'])
            ->orderByDesc('created_at');

        if ($this->partyId) {
            $query->where('party_id', $this->partyId);
        }

        $search = trim($this->q);
        if ($search !== '') {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }

        return view('livewire.admin.stock-index', [
            'parties' => $parties,
            'stocks' => $query->paginate(20),
            'selectedParty' => $this->partyId ? Party::find($this->partyId) : null,
        ]);
    }
}
