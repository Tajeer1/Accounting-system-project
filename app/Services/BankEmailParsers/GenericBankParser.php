<?php

namespace App\Services\BankEmailParsers;

use Carbon\Carbon;

class GenericBankParser extends AbstractBankEmailParser
{
    public function key(): string
    {
        return 'generic';
    }

    public function bankName(): string
    {
        return 'Generic Bank';
    }

    public function supports(string $sender, string $subject, string $body): bool
    {
        return true;
    }

    public function parse(string $sender, string $subject, string $body, ?\DateTimeInterface $receivedAt = null): ?array
    {
        $body = $this->normalizeBody($body);

        $type = $this->detectTransactionType($subject . "\n" . $body);

        [$amount, $currency] = $this->parseAmount($body);

        if ($amount === null) {
            return null;
        }

        $description = $this->captureAfterLabel($body, 'Description')
            ?? $this->captureAfterLabel($body, 'Narration')
            ?? $this->captureAfterLabel($body, 'البيان')
            ?? mb_substr(trim($subject), 0, 200);

        $datetime = $receivedAt ? Carbon::instance($receivedAt) : Carbon::now();

        return [
            'bank_name' => null,
            'transaction_type' => $type,
            'masked_card_number' => null,
            'masked_account_number' => $this->captureAfterLabel($body, 'Account')
                ?? $this->captureAfterLabel($body, 'Account number'),
            'description' => $description,
            'amount' => $amount,
            'currency' => $currency ?? 'OMR',
            'transaction_datetime' => $datetime->format('Y-m-d H:i:s'),
            'transaction_country' => null,
            'balance_after' => null,
        ];
    }

    protected function parseAmount(string $body): array
    {
        $line = $this->captureAfterLabel($body, 'Amount')
            ?? $this->captureAfterLabel($body, 'المبلغ');

        if ($line !== null) {
            if (preg_match('/([A-Z]{3})\s*([0-9][0-9,]*\.?[0-9]*)/i', $line, $m)) {
                return [(float) str_replace(',', '', $m[2]), strtoupper($m[1])];
            }
            if (preg_match('/([0-9][0-9,]*\.?[0-9]*)\s*([A-Z]{3})/i', $line, $m)) {
                return [(float) str_replace(',', '', $m[1]), strtoupper($m[2])];
            }
            if (preg_match('/([0-9][0-9,]*\.?[0-9]*)/', $line, $m)) {
                return [(float) str_replace(',', '', $m[1]), null];
            }
        }

        if (preg_match('/([A-Z]{3})\s*([0-9][0-9,]*\.[0-9]+)/i', $body, $m)) {
            return [(float) str_replace(',', '', $m[2]), strtoupper($m[1])];
        }

        return [null, null];
    }
}
