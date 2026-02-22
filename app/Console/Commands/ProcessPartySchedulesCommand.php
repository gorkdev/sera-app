<?php

namespace App\Console\Commands;

use App\Models\Party;
use Illuminate\Console\Command;

class ProcessPartySchedulesCommand extends Command
{
    protected $signature = 'parties:process-schedules';

    protected $description = 'Parti başlangıç/bitiş tarihlerine göre otomatik aktivasyon ve kapatma işler';

    public function handle(): int
    {
        $now = now();

        // Başlangıç tarihi gelmiş taslak partileri aktif et (aynı anda tek parti: önce diğerini kapat)
        $toActivate = Party::where('status', 'draft')
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', $now)
            ->orderBy('starts_at')
            ->get();

        foreach ($toActivate as $party) {
            $otherActive = Party::where('status', 'active')->where('id', '!=', $party->id)->first();
            if ($otherActive) {
                $otherActive->markClosed();
                $this->info("Önceki parti \"{$otherActive->name}\" kapatıldı.");
            }
            $party->update([
                'status' => 'active',
                'activated_at' => $party->activated_at ?? $now,
            ]);
            $this->info("Parti \"{$party->name}\" otomatik aktif edildi (starts_at: {$party->starts_at}).");
        }

        // Bitiş tarihi gelmiş aktif partileri kapat
        $toCloseByDate = Party::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', $now)
            ->get();

        foreach ($toCloseByDate as $party) {
            $party->markClosed();
            $this->info("Parti \"{$party->name}\" otomatik kapatıldı (ends_at: {$party->ends_at}).");
        }

        // Stok biten aktif partileri kapat (close_when_stock_runs_out)
        $activeWithStockCheck = Party::where('status', 'active')
            ->where('close_when_stock_runs_out', true)
            ->get();

        foreach ($activeWithStockCheck as $party) {
            if (!$party->hasAvailableStock()) {
                $party->markClosed();
                $this->info("Parti \"{$party->name}\" stok bittiği için otomatik kapatıldı.");
            }
        }

        return self::SUCCESS;
    }
}
