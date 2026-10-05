<?php

namespace App\Console\Commands;

use App\Services\BookingWorkflowService;
use Illuminate\Console\Command;

class CompleteFinishedBookings extends Command
{
    protected $signature = 'bookings:complete';

    protected $description = 'Tandai peminjaman yang telah melewati batas waktu selesai sebagai SELESAI';

    public function handle(BookingWorkflowService $workflowService): int
    {
        $this->info('Memeriksa peminjaman yang telah selesai...');
        $count = $workflowService->completeFinishedBookings();
        $this->info("Berhasil memperbarui {$count} peminjaman menjadi SELESAI.");

        return Command::SUCCESS;
    }
}
