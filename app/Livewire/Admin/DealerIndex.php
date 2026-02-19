<?php

namespace App\Livewire\Admin;

use App\Models\Dealer;
use Livewire\Component;
use Livewire\WithPagination;

class DealerIndex extends Component
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

    public function approve(int $dealerId): void
    {
        $dealer = Dealer::findOrFail($dealerId);

        if (! $dealer->email_verified_at) {
            session()->flash('error', 'Bayi e-posta adresini doğrulamadan onaylanamaz.');

            return;
        }

        $dealer->status = 'active';
        $dealer->save();

        session()->flash('success', 'Bayi üyeliği onaylandı.');
    }

    public function reject(int $dealerId): void
    {
        $dealer = Dealer::findOrFail($dealerId);
        $dealer->status = 'passive';
        $dealer->save();

        session()->flash('success', 'Bayi üyeliği reddedildi/pasife alındı.');
    }


    public function render()
    {
        $query = Dealer::query()
            ->with('group')
            ->orderByDesc('created_at');

        if ($this->status !== '' && in_array($this->status, ['pending', 'active', 'passive'], true)) {
            $query->where('status', $this->status);
        }

        $search = trim($this->q);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', '%' . $search . '%')
                    ->orWhere('contact_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%')
                    ->orWhere('district', 'like', '%' . $search . '%');
            });
        }

        return view('livewire.admin.dealer-index', [
            'dealers' => $query->paginate(20),
        ]);
    }
}

