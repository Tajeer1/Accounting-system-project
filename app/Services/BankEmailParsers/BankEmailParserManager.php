<?php

namespace App\Services\BankEmailParsers;

use App\Models\BankAccount;
use App\Models\BankEmailMessage;
use App\Models\BankTransaction;
use Illuminate\Support\Facades\Log;

class BankEmailParserManager
{
    /** @var BankEmailParserInterface[] */
    private array $parsers = [];

    private GenericBankParser $fallback;

    public function __construct(GenericBankParser $fallback)
    {
        $this->fallback = $fallback;
    }

    public function register(BankEmailParserInterface $parser): void
    {
        $this->parsers[] = $parser;
    }

    public function selectParser(BankEmailMessage $message): BankEmailParserInterface
    {
        foreach ($this->parsers as $parser) {
            if ($parser->matches($message)) {
                return $parser;
            }
        }
        return $this->fallback;
    }

    /**
     * Parse a stored email and persist resulting transactions.
     * Returns the number of transactions persisted.
     */
    public function processMessage(BankEmailMessage $message): int
    {
        $parser = $this->selectParser($message);

        try {
            $parsed = $parser->parse($message);
        } catch (\Throwable $e) {
            $message->update([
                'status' => 'failed',
                'parse_error' => $e->getMessage(),
                'bank_key' => $parser->key(),
            ]);
            Log::warning('Bank email parse failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }

        if (empty($parsed)) {
            $message->update([
                'status' => 'ignored',
                'bank_key' => $parser->key(),
            ]);
            return 0;
        }

        $bankAccountId = $message->bank_account_id ?? $this->guessBankAccount($message)?->id;
        if ($bankAccountId && ! $message->bank_account_id) {
            $message->bank_account_id = $bankAccountId;
        }

        $count = 0;
        foreach ($parsed as $tx) {
            $payload = array_merge($tx->toArray(), [
                'bank_email_message_id' => $message->id,
                'bank_account_id' => $bankAccountId,
                'status' => 'pending_review',
            ]);

            BankTransaction::firstOrCreate(
                [
                    'bank_account_id' => $bankAccountId,
                    'reference' => $tx->reference,
                    'transaction_date' => $tx->transactionDate,
                    'amount' => $tx->amount,
                ],
                $payload
            );
            $count++;
        }

        $message->update([
            'status' => 'parsed',
            'bank_key' => $parser->key(),
            'parse_error' => null,
        ]);

        return $count;
    }

    private function guessBankAccount(BankEmailMessage $message): ?BankAccount
    {
        $domain = strtolower(substr(strrchr((string) $message->from_email, '@') ?: '', 1));
        if ($domain === '') {
            return null;
        }

        return BankAccount::query()
            ->where('is_active', true)
            ->where(function ($q) use ($domain, $message) {
                $q->where('email_match_sender', 'like', "%{$domain}%")
                  ->orWhere('email_match_sender', 'like', '%'.$message->from_email.'%');
            })
            ->first();
    }
}
