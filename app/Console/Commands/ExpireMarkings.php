<?php

namespace App\Console\Commands;

use App\Services\MarkingService;
use Illuminate\Console\Command;

class ExpireMarkings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marking:expire {--notify= : Hours before expiration to send notification (default: 24)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire markings that have passed their expiration date and send notifications for markings about to expire';

    /**
     * The marking service instance.
     *
     * @var \App\Services\MarkingService
     */
    protected $markingService;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\MarkingService  $markingService
     * @return void
     */
    public function __construct(MarkingService $markingService)
    {
        parent::__construct();
        $this->markingService = $markingService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting marking expiration process...');

        // Auto expire markings
        $expiredCount = $this->markingService->autoExpireMarkings();
        $this->info("Expired {$expiredCount} markings.");

        // Send notifications for markings about to expire
        $hoursThreshold = $this->option('notify') ? (int) $this->option('notify') : 24;
        $notificationCount = $this->markingService->sendExpirationReminders($hoursThreshold);
        $this->info("Sent {$notificationCount} expiration notifications for markings expiring within {$hoursThreshold} hours.");

        $this->info('Marking expiration process completed.');

        return 0;
    }
}







