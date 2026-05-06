<?php

namespace App\Console\Commands;

use App\Jobs\FetchBankEmailsJob;
use App\Models\BankEmailMessage;
use App\Models\GmailAccount;
use App\Services\BankEmailParsers\BankEmailParserManager;
use App\Services\Gmail\GmailFetcher;
use Illuminate\Console\Command;

class FetchBankEmailsCommand extends Command
{
    protected $signature = 'bank-emails:fetch
                            {--account= : Specific Gmail account email}
                            {--queue : Dispatch as queued job instead of running synchronously}';

    protected $description = 'Fetch bank emails from connected Gmail accounts and parse them';

    public function handle(GmailFetcher $fetcher, BankEmailParserManager $manager): int
    {
        if ($this->option('queue')) {
            FetchBankEmailsJob::dispatch();
            $this->info('Job dispatched.');
            return self::SUCCESS;
        }

        $query = GmailAccount::where('is_active', true)->whereNotNull('refresh_token');
        if ($email = $this->option('account')) {
            $query->where('email', $email);
        }
        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            $this->warn('No active Gmail accounts found.');
            return self::SUCCESS;
        }

        $totalFetched = 0;
        foreach ($accounts as $account) {
            $this->info("Syncing {$account->email}...");
            try {
                $count = $fetcher->fetchForAccount($account);
                $totalFetched += $count;
                $this->line("  → fetched {$count} new messages");
            } catch (\Throwable $e) {
                $this->error("  → failed: {$e->getMessage()}");
            }
        }

        $this->info("Parsing pending emails...");
        $pending = BankEmailMessage::where('status', 'pending')->get();
        $parsed = 0;
        foreach ($pending as $message) {
            $parsed += $manager->processMessage($message);
        }

        $this->info("Done. Fetched {$totalFetched} new messages, extracted {$parsed} transactions.");
        return self::SUCCESS;
    }
}
