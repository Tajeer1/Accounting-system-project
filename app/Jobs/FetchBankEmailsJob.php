<?php

namespace App\Jobs;

use App\Models\BankEmailMessage;
use App\Models\GmailAccount;
use App\Services\BankEmailParsers\BankEmailParserManager;
use App\Services\Gmail\GmailFetcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchBankEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 2;

    public function handle(GmailFetcher $fetcher, BankEmailParserManager $manager): void
    {
        $accounts = GmailAccount::where('is_active', true)
            ->whereNotNull('refresh_token')
            ->get();

        if ($accounts->isEmpty()) {
            Log::info('FetchBankEmailsJob: no active Gmail accounts to sync.');
            return;
        }

        foreach ($accounts as $account) {
            try {
                $fetched = $fetcher->fetchForAccount($account);
                Log::info("FetchBankEmailsJob: fetched {$fetched} new messages for {$account->email}");
            } catch (\Throwable $e) {
                Log::error('FetchBankEmailsJob fetch failed', [
                    'account' => $account->email,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        $pending = BankEmailMessage::where('status', 'pending')->limit(100)->get();
        foreach ($pending as $message) {
            $manager->processMessage($message);
        }
    }
}
