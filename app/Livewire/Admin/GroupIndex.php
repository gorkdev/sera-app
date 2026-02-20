<?php

namespace App\Livewire\Admin;

use App\Models\DealerGroup;
use Livewire\Component;
use Livewire\WithPagination;

class GroupIndex extends Component
{
    use WithPagination;

    public string $q = '';

    protected $queryString = [
        'q' => ['except' => ''],
    ];

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->q = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = DealerGroup::query()
            ->withCount('dealers')
            ->orderBy('sort_order')
            ->orderBy('name');

        $search = trim($this->q);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        return view('livewire.admin.group-index', [
            'groups' => $query->paginate(20),
        ]);
    }
}
