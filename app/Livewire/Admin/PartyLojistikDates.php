<?php

namespace App\Livewire\Admin;

use Carbon\Carbon;
use Livewire\Component;

class PartyLojistikDates extends Component
{
    public ?string $truck_status = '';

    public ?string $departure_at = '';

    public ?string $arrived_at = '';

    public ?string $florist_delivery_at = '';

    public function mount(?string $truck_status = '', ?string $departure_at = null, ?string $arrived_at = null, ?string $florist_delivery_at = null): void
    {
        $this->truck_status = $truck_status ?? '';
        $this->departure_at = $departure_at ?? '';
        $this->arrived_at = $arrived_at ?? '';
        $defaultFlorist = $florist_delivery_at ?? $this->defaultFloristFromArrival($arrived_at);
        $this->florist_delivery_at = $defaultFlorist ?? '';
    }

    public function updatedArrivedAt(): void
    {
        if (empty($this->arrived_at)) {
            return;
        }
        try {
            $date = Carbon::parse($this->arrived_at);
            $this->florist_delivery_at = $date->addDay()->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            // Invalid date, ignore
        }
    }

    private function defaultFloristFromArrival(?string $arrived): string
    {
        if (empty($arrived)) {
            return '';
        }
        try {
            return Carbon::parse($arrived)->addDay()->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return '';
        }
    }

    public function render()
    {
        return view('livewire.admin.party-lojistik-dates');
    }
}
