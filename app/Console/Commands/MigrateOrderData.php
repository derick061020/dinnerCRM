<?php

namespace App\Console\Commands;

use App\Services\OrderDataMigrationService;
use Illuminate\Console\Command;

class MigrateOrderData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:migrate-data {--force : Force migration without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate order data from JSON to individual columns';

    /**
     * Execute the console command.
     */
    public function handle(OrderDataMigrationService $migrationService): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will migrate all existing order data from JSON to individual columns. Do you want to continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Starting order data migration...');

        try {
            $migratedCount = $migrationService->migrateAllOrders();
            
            $this->info("Successfully migrated {$migratedCount} orders.");
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }
    }
}
