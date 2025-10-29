<?php

namespace App\Console;

use App\Models\Peminjaman;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        
        // Run marking expiration check every hour
        $schedule->command('marking:expire')->hourly();
        
        // Send notification for markings expiring in 24 hours (once a day at 8 AM)
        $schedule->command('marking:expire --notify=24')->dailyAt('08:00');

        // Purge notifikasi expired setiap hari jam 01:00
        $schedule->command('notifications:purge-expired')->dailyAt('01:00');

        $schedule->call(function () {
            $service = app(NotificationService::class);

            $tomorrowPickup = Peminjaman::with('user')
                ->whereDate('start_date', Carbon::tomorrow())
                ->whereIn('status', [
                    Peminjaman::STATUS_APPROVED,
                ])->get();

            if ($tomorrowPickup->isNotEmpty()) {
                $service->notifyPickupReminders($tomorrowPickup->all());
            }

            $todayPickup = Peminjaman::with('user')
                ->whereDate('start_date', Carbon::today())
                ->whereIn('status', [
                    Peminjaman::STATUS_APPROVED,
                ])->get();

            if ($todayPickup->isNotEmpty()) {
                $service->notifyPickupReminders($todayPickup->all());
            }
        })->dailyAt('07:00');

        $schedule->call(function () {
            $service = app(NotificationService::class);

            $tomorrowReturn = Peminjaman::with('user')
                ->whereDate('end_date', Carbon::tomorrow())
                ->whereIn('status', [
                    Peminjaman::STATUS_PICKED_UP,
                ])->get();

            if ($tomorrowReturn->isNotEmpty()) {
                $service->notifyReturnReminders($tomorrowReturn->all());
            }

            $todayReturn = Peminjaman::with('user')
                ->whereDate('end_date', Carbon::today())
                ->whereIn('status', [
                    Peminjaman::STATUS_PICKED_UP,
                ])->get();

            if ($todayReturn->isNotEmpty()) {
                $service->notifyReturnReminders($todayReturn->all());
            }
        })->dailyAt('18:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
