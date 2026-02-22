<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\OrderStatus;
use Livewire\Component;
use Livewire\WithPagination;

class OrderIndex extends Component
{
    use WithPagination;

    public string $statusSlug = '';
    public string $orderNumber = '';
    public string $dealerSearch = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    protected $queryString = [
        'statusSlug' => ['except' => ''],
        'orderNumber' => ['except' => ''],
        'dealerSearch' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingStatusSlug(): void
    {
        $this->resetPage();
    }

    public function updatingOrderNumber(): void
    {
        $this->resetPage();
    }

    public function updatingDealerSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->statusSlug = '';
        $this->orderNumber = '';
        $this->dealerSearch = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Order::query()
            ->with(['dealer:id,company_name,contact_name,email', 'party:id,name,party_code', 'orderStatus', 'items.product'])
            ->orderByDesc('created_at');

        if ($this->statusSlug !== '') {
            $query->whereHas('orderStatus', fn ($q) => $q->where('slug', $this->statusSlug));
        }

        $orderNum = trim($this->orderNumber);
        if ($orderNum !== '') {
            $query->where('order_number', 'like', '%' . $orderNum . '%');
        }

        $dealerQ = trim($this->dealerSearch);
        if ($dealerQ !== '') {
            $query->whereHas('dealer', function ($q) use ($dealerQ) {
                $q->where('company_name', 'like', '%' . $dealerQ . '%')
                    ->orWhere('contact_name', 'like', '%' . $dealerQ . '%')
                    ->orWhere('email', 'like', '%' . $dealerQ . '%');
            });
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo !== '') {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $orders = $query->paginate(20);
        $statuses = OrderStatus::orderBy('sort_order')->get();

        return view('livewire.admin.order-index', [
            'orders' => $orders,
            'statuses' => $statuses,
        ]);
    }
}
