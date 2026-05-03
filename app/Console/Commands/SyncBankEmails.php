<?php

namespace App\Console\Commands;

use App\Models\BankEmailIntegration;
use App\Services\BankEmailService;
use Illuminate\Console\Command;

class SyncBankEmails extends Command
{
    protected $signature = 'bank-emails:sync
                            {--integration= : Sync a specific integration ID only}
                            {--limit=100 : Maximum messages to fetch per integration}';

    protected $description = 'Fetch new bank notification emails and import them as bank transactions';

    public function handle(BankEmailService $service): int
    {
        $query = BankEmailIntegration::query()->where('is_active', true);

        if ($id = $this->option('integration')) {
            $query->where('id', $id);
        }

        $integrations = $query->get();

        if ($integrations->isEmpty()) {
            $this->info('No active integrations to sync.');
            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        $exitCode = self::SUCCESS;

        foreach ($integrations as $integration) {
            $this->line("→ Syncing #{$integration->id} {$integration->bank_name} <{$integration->email_address}>");

            $stats = $service->syncIntegration($integration, $limit);

            if (! ($stats['success'] ?? false)) {
                $this->error("  ✗ {$stats['error']}");
                $exitCode = self::FAILURE;
                continue;
            }

            $this->info(sprintf(
                '  ✓ fetched=%d processed=%d duplicates=%d failed=%d',
                $stats['fetched'] ?? 0,
                $stats['processed'] ?? 0,
                $stats['duplicates'] ?? 0,
                $stats['failed'] ?? 0,
            ));
        }

        return $exitCode;
    }
}
