<?php

namespace App\Services;

use App\Models\BankEmailIntegration;
use App\Models\BankEmailMessage;
use App\Models\BankTransaction;
use App\Models\Invoice;
use App\Models\Purchase;
use App\Services\BankEmailParsers\ParserResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;
use Webklex\PHPIMAP\ClientManager;

class BankEmailService
{
    public function __construct(
        protected ParserResolver $parsers,
        protected AccountingService $accounting,
    ) {}

    /**
     * @return array{success:bool, fetched:int, processed:int, failed:int, duplicates:int, error?:string}
     */
    public function syncIntegration(BankEmailIntegration $integration, int $limit = 100): array
    {
        $stats = ['success' => true, 'fetched' => 0, 'processed' => 0, 'failed' => 0, 'duplicates' => 0];

        if (! $integration->is_active) {
            $stats['success'] = false;
            $stats['error'] = 'integration disabled';
            return $stats;
        }

        try {
            $client = $this->makeClient($integration);
            $client->connect();

            $folder = $client->getFolder($integration->mailbox_folder ?: 'INBOX');
            if ($folder === null) {
                throw new \RuntimeException("Mailbox folder not found: {$integration->mailbox_folder}");
            }

            $query = $folder->messages()->unseen();

            if ($integration->sender_filter) {
                $query->from($integration->sender_filter);
            }
            if ($integration->keyword_filter) {
                $query->subject($integration->keyword_filter);
            }

            $messages = $query->limit($limit)->setFetchOrder('asc')->get();
            $stats['fetched'] = $messages->count();

            foreach ($messages as $imapMessage) {
                try {
                    $result = $this->processMessage($integration, $imapMessage);
                    $stats[$result] = ($stats[$result] ?? 0) + 1;
                } catch (Throwable $e) {
                    $stats['failed']++;
                    Log::error('bank-emails: message processing failed', [
                        'integration_id' => $integration->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $integration->update([
                'last_synced_at' => now(),
                'last_sync_error' => null,
            ]);
        } catch (Throwable $e) {
            $stats['success'] = false;
            $stats['error'] = $e->getMessage();
            $integration->update([
                'last_synced_at' => now(),
                'last_sync_error' => mb_substr($e->getMessage(), 0, 500),
            ]);
            Log::error('bank-emails: connection failed', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $stats;
    }

    /**
     * Test the IMAP connection only, without fetching messages.
     *
     * @return array{success:bool, error?:string, folders?:int}
     */
    public function testConnection(BankEmailIntegration $integration): array
    {
        try {
            $client = $this->makeClient($integration);
            $client->connect();
            $folders = $client->getFolders();
            return ['success' => true, 'folders' => $folders->count()];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process a single IMAP message: dedupe, save, parse, create transaction.
     *
     * @return string one of: processed, duplicates, failed
     */
    protected function processMessage(BankEmailIntegration $integration, $imapMessage): string
    {
        $uid = (string) $imapMessage->getUid();
        $messageId = (string) $imapMessage->getMessageId();
        $subject = (string) $imapMessage->getSubject();
        $sender = $this->extractSender($imapMessage);
        $body = $this->extractBody($imapMessage);
        $receivedAt = $this->extractDate($imapMessage);
        $contentHash = hash('sha256', $sender . '|' . $subject . '|' . $body);

        $existing = BankEmailMessage::where('integration_id', $integration->id)
            ->where(function ($q) use ($uid, $contentHash) {
                $q->where('message_uid', $uid)->orWhere('content_hash', $contentHash);
            })->first();

        if ($existing) {
            return 'duplicates';
        }

        $emailMessage = BankEmailMessage::create([
            'integration_id' => $integration->id,
            'message_uid' => $uid,
            'message_id' => $messageId,
            'sender' => $sender,
            'subject' => $subject,
            'received_at' => $receivedAt,
            'raw_body' => mb_substr($body, 0, 65000),
            'content_hash' => $contentHash,
            'processing_status' => 'pending',
        ]);

        try {
            $parser = $this->parsers->resolve($integration->parser_key, $sender, $subject, $body);
            $parsed = $parser->parse($sender, $subject, $body, $receivedAt);

            if ($parsed === null) {
                $emailMessage->update([
                    'processing_status' => 'failed',
                    'error_message' => 'Parser returned null (could not extract amount or date)',
                ]);
                return 'failed';
            }

            if (empty($parsed['amount']) || empty($parsed['transaction_datetime'])) {
                $emailMessage->update([
                    'processing_status' => 'failed',
                    'error_message' => 'Missing required fields after parsing',
                ]);
                return 'failed';
            }

            $this->createTransaction($integration, $emailMessage, $parsed);

            $emailMessage->update(['processing_status' => 'processed']);

            if ($integration->mark_seen_after_import) {
                try {
                    $imapMessage->setFlag('Seen');
                } catch (Throwable $e) {
                    Log::warning('bank-emails: failed to mark message as seen', [
                        'message_id' => $messageId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return 'processed';
        } catch (Throwable $e) {
            $emailMessage->update([
                'processing_status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 500),
            ]);
            Log::error('bank-emails: parse failed', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
            return 'failed';
        }
    }

    protected function createTransaction(BankEmailIntegration $integration, BankEmailMessage $message, array $parsed): BankTransaction
    {
        $amount = (float) $parsed['amount'];
        $datetime = $parsed['transaction_datetime'];
        $type = $parsed['transaction_type'] ?? 'unknown';

        $dedupeHash = hash('sha256', implode('|', [
            $integration->id,
            $type,
            number_format($amount, 3, '.', ''),
            $parsed['currency'] ?? 'OMR',
            (string) $datetime,
            (string) ($parsed['masked_account_number'] ?? ''),
            (string) ($parsed['description'] ?? ''),
        ]));

        $existing = BankTransaction::where('dedupe_hash', $dedupeHash)->first();
        if ($existing) {
            return $existing;
        }

        $shouldAutoConfirm = $integration->auto_confirm && $type !== 'unknown';

        $tx = BankTransaction::create([
            'bank_account_id' => $integration->linked_bank_account_id,
            'source' => 'email',
            'source_message_id' => $message->id,
            'bank_name' => $parsed['bank_name'] ?? $integration->bank_name,
            'transaction_type' => $type,
            'masked_card_number' => $parsed['masked_card_number'] ?? null,
            'masked_account_number' => $parsed['masked_account_number'] ?? null,
            'description' => $parsed['description'] ?? null,
            'amount' => $amount,
            'currency' => $parsed['currency'] ?? 'OMR',
            'balance_after' => $parsed['balance_after'] ?? null,
            'transaction_country' => $parsed['transaction_country'] ?? null,
            'transaction_datetime' => $datetime,
            'status' => 'pending_review',
            'dedupe_hash' => $dedupeHash,
            'raw_data' => $parsed,
        ]);

        $this->suggestMatch($tx);

        if ($shouldAutoConfirm) {
            $this->confirmTransaction($tx);
        }

        return $tx;
    }

    /**
     * Try to find a single unambiguous match for the transaction. If multiple
     * candidates are found, no automatic match is set — admin reviews manually.
     */
    public function suggestMatch(BankTransaction $tx): void
    {
        $tolerance = 0.01;

        if ($tx->transaction_type === 'debit') {
            $candidates = Purchase::query()
                ->whereBetween('amount', [(float) $tx->amount - $tolerance, (float) $tx->amount + $tolerance])
                ->where('status', '!=', 'cancelled');

            if ($tx->transaction_datetime) {
                $from = $tx->transaction_datetime->copy()->subDays(2)->toDateString();
                $to = $tx->transaction_datetime->copy()->addDays(2)->toDateString();
                $candidates->whereBetween('purchase_date', [$from, $to]);
            }

            $found = $candidates->limit(2)->get();
            if ($found->count() === 1) {
                $tx->matched_purchase_id = $found->first()->id;
                $tx->save();
            }
            return;
        }

        if ($tx->transaction_type === 'credit') {
            $found = Invoice::query()
                ->where('type', 'sales')
                ->where('status', '!=', 'paid')
                ->whereBetween('amount', [(float) $tx->amount - $tolerance, (float) $tx->amount + $tolerance])
                ->limit(2)
                ->get();

            if ($found->count() === 1) {
                $tx->matched_invoice_id = $found->first()->id;
                $tx->save();
            }
        }
    }

    /**
     * Get suggested matches for review UI (multiple candidates).
     *
     * @return \Illuminate\Support\Collection
     */
    public function findMatchCandidates(BankTransaction $tx): \Illuminate\Support\Collection
    {
        $tolerance = 0.01;
        $rangeMin = (float) $tx->amount - $tolerance;
        $rangeMax = (float) $tx->amount + $tolerance;

        if ($tx->transaction_type === 'debit') {
            return Purchase::query()
                ->whereBetween('amount', [$rangeMin, $rangeMax])
                ->where('status', '!=', 'cancelled')
                ->limit(10)
                ->get();
        }

        if ($tx->transaction_type === 'credit') {
            return Invoice::query()
                ->where('type', 'sales')
                ->where('status', '!=', 'paid')
                ->whereBetween('amount', [$rangeMin, $rangeMax])
                ->limit(10)
                ->get();
        }

        return collect();
    }

    /**
     * Confirm the transaction: optionally create a journal entry and recalculate
     * the linked bank account balance.
     */
    public function confirmTransaction(BankTransaction $tx, bool $createJournal = true): BankTransaction
    {
        $tx->update(['status' => 'confirmed']);

        if ($createJournal && $tx->bank_account_id) {
            $entry = $this->accounting->createEntryForBankTransaction($tx);
            if ($entry) {
                $tx->journal_entry_id = $entry->id;
                $tx->save();
            }
        }

        if ($tx->bank_account_id && $tx->bankAccount) {
            $tx->bankAccount->recalculateBalance();
        }

        return $tx;
    }

    public function ignoreTransaction(BankTransaction $tx): BankTransaction
    {
        $tx->update(['status' => 'ignored']);
        return $tx;
    }

    protected function makeClient(BankEmailIntegration $integration)
    {
        $cm = new ClientManager();

        return $cm->make([
            'host' => $integration->imap_host,
            'port' => $integration->imap_port,
            'encryption' => $integration->encryption === 'none' ? false : $integration->encryption,
            'validate_cert' => $integration->validate_cert,
            'username' => $integration->username,
            'password' => $integration->password,
            'protocol' => 'imap',
            'authentication' => null,
        ]);
    }

    protected function extractSender($imapMessage): string
    {
        try {
            $from = $imapMessage->getFrom();
            if (is_array($from) && count($from) > 0) {
                $first = $from[0];
                if (is_object($first)) {
                    $email = $first->mail ?? ($first->personal ?? '');
                    return is_string($email) ? $email : '';
                }
            }
        } catch (Throwable) {
            // fallthrough
        }
        return '';
    }

    protected function extractBody($imapMessage): string
    {
        try {
            $text = $imapMessage->getTextBody();
            if (is_string($text) && trim($text) !== '') {
                return $text;
            }
        } catch (Throwable) {}

        try {
            $html = $imapMessage->getHTMLBody();
            if (is_string($html) && trim($html) !== '') {
                return trim(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $html)));
            }
        } catch (Throwable) {}

        return '';
    }

    protected function extractDate($imapMessage): ?Carbon
    {
        try {
            $date = $imapMessage->getDate();
            if ($date instanceof \DateTimeInterface) {
                return Carbon::instance($date);
            }
            if (is_string($date)) {
                return Carbon::parse($date);
            }
        } catch (Throwable) {}
        return null;
    }
}
