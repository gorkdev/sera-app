<?php

namespace App\Console\Commands;

use App\Services\PartyScheduleService;
use Illuminate\Console\Command;

class ProcessPartySchedulesCommand extends Command
{
    protected $signature = 'parties:process-schedules';

    protected $description = 'Parti başlangıç/bitiş tarihlerine göre otomatik aktivasyon ve kapatma işler';

    public function handle(PartyScheduleService $scheduleService): int
    {
        $scheduleService->processSchedules();

        return self::SUCCESS;
    }
}
