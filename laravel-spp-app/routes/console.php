<?php

use App\Console\Commands\GenerateMonthlyInvoicesCommand;
use Illuminate\Support\Facades\Schedule;

/**
 * Laravel 13 - schedule di routes/console.php.
 * Cron: setiap tanggal 1 pukul 00:00 waktu server (Asia/Jakarta).
 */
Schedule::command(GenerateMonthlyInvoicesCommand::class)
    ->monthlyOn(1, '00:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->onOneServer();
