<?php

namespace App\Console\Commands;

use App\Models\Sarana;
use Illuminate\Console\Command;

class UpdateSaranaStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sarana:update-stats {--id= : Update specific sarana by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update statistics for all sarana or specific sarana';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $saranaId = $this->option('id');
        
        if ($saranaId) {
            $sarana = Sarana::find($saranaId);
            if (!$sarana) {
                $this->error("Sarana with ID {$saranaId} not found.");
                return 1;
            }
            
            $this->info("Updating stats for sarana: {$sarana->name}");
            $sarana->updateStats();
            $this->info("Stats updated successfully.");
            
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $sarana->id],
                    ['Name', $sarana->name],
                    ['Type', $sarana->type],
                    ['Total', $sarana->jumlah_total],
                    ['Available', $sarana->jumlah_tersedia],
                    ['Broken', $sarana->jumlah_rusak],
                    ['Maintenance', $sarana->jumlah_maintenance],
                    ['Lost', $sarana->jumlah_hilang],
                ]
            );
        } else {
            $this->info('Updating stats for all sarana...');
            
            $sarana = Sarana::all();
            $bar = $this->output->createProgressBar($sarana->count());
            $bar->start();
            
            $updated = 0;
            $errors = 0;
            
            foreach ($sarana as $s) {
                try {
                    $s->updateStats();
                    $updated++;
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("\nError updating sarana {$s->id}: " . $e->getMessage());
                }
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->info("Updated: {$updated} sarana");
            if ($errors > 0) {
                $this->error("Errors: {$errors} sarana");
            }
        }
        
        return 0;
    }
}



