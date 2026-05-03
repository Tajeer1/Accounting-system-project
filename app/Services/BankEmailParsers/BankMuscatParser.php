<?php

namespace App\Services\BankEmailParsers;

use Carbon\Carbon;

class BankMuscatParser extends AbstractBankEmailParser
{
    public function key(): string
    {
        return 'bank_muscat';
    }

    public function bankName(): string
    {
        return 'Bank Muscat';
    }

    public function supports(string $sender, string $subject, string $body): bool
    {
        $haystack = mb_strtolower($sender . ' ' . $subject . ' ' . $body);

        return str_contains($haystack, 'bank muscat')
            || str_contains($haystack, 'bankmuscat')
            || str_contains($haystack, 'bm.com')
            || str_contains($haystack, '@bankmuscat');
    }

    public function parse(string $sender, string $subject, string $body, ?\DateTimeInterface $receivedAt = null): ?array
    {
        $body = $this->normalizeBody($body);

        $type = $this->detectTransactionType($subject . "\n" . $body);

        $accountNumber = $this->captureAfterLabel($body, 'Account number')
            ?? $this->captureAfterLabel($body, 'Account No');

        $description = $this->captureAfterLabel($body, 'Description');

        [$amount, $currency] = $this->parseAmountLine($body);

        $datetime = $this->parseDateTimeLine($body, $receivedAt);

        $country = $this->captureAfterLabel($body, 'Transaction Country')
            ?? $this->captureAfterLabel($body, 'Country');

        $maskedCard = $this->parseMaskedCard($body);

        $balanceAfter = $this->parseBalance($body);

        if ($amount === null || $datetime === null) {
            return null;
        }

        return [
            'bank_name' => $this->bankName(),
            'transaction_type' => $type,
            'masked_card_number' => $maskedCard,
            'masked_account_number' => $accountNumber,
            'description' => $description,
            'amount' => $amount,
            'currency' => $currency ?? 'OMR',
            'transaction_datetime' => $datetime->format('Y-m-d H:i:s'),
            'transaction_country' => $country,
            'balance_after' => $balanceAfter,
        ];
    }

    protected function parseAmountLine(string $body): array
    {
        $line = $this->captureAfterLabel($body, 'Amount');
        if ($line === null) {
            return [null, null];
        }

        if (preg_match('/([A-Z]{3})\s*([0-9][0-9,]*\.?[0-9]*)/i', $line, $m)) {
            return [(float) str_replace(',', '', $m[2]), strtoupper($m[1])];
        }

        if (preg_match('/([0-9][0-9,]*\.?[0-9]*)\s*([A-Z]{3})/i', $line, $m)) {
            return [(float) str_replace(',', '', $m[1]), strtoupper($m[2])];
        }

        if (preg_match('/([0-9][0-9,]*\.?[0-9]*)/', $line, $m)) {
            return [(float) str_replace(',', '', $m[1]), null];
        }

        return [null, null];
    }

    protected function parseDateTimeLine(string $body, ?\DateTimeInterface $receivedAt): ?Carbon
    {
        $line = $this->captureAfterLabel($body, 'Date/Time')
            ?? $this->captureAfterLabel($body, 'Date / Time')
            ?? $this->captureAfterLabel($body, 'Date');

        if ($line === null) {
            return $receivedAt ? Carbon::instance($receivedAt) : null;
        }

        $formats = [
            'd M y H:i',
            'd M Y H:i',
            'd-M-y H:i',
            'd-M-Y H:i',
            'd/m/Y H:i',
            'Y-m-d H:i:s',
        ];

        foreach ($formats as $format) {
            try {
                $dt = Carbon::createFromFormat($format, trim(strtoupper($line)));
                if ($dt instanceof Carbon) {
                    return $dt;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($line);
        } catch (\Throwable) {
            return $receivedAt ? Carbon::instance($receivedAt) : null;
        }
    }

    protected function parseMaskedCard(string $body): ?string
    {
        if (preg_match('/Debit card number\s+([0-9X*\s]+[0-9*]+)/i', $body, $m)) {
            return trim(preg_replace('/\s+/', ' ', $m[1]));
        }
        if (preg_match('/Credit card number\s+([0-9X*\s]+[0-9*]+)/i', $body, $m)) {
            return trim(preg_replace('/\s+/', ' ', $m[1]));
        }
        return null;
    }

    protected function parseBalance(string $body): ?float
    {
        $line = $this->captureAfterLabel($body, 'Available Balance')
            ?? $this->captureAfterLabel($body, 'Remaining Balance')
            ?? $this->captureAfterLabel($body, 'Balance');

        if ($line === null) {
            return null;
        }

        if (preg_match('/([0-9][0-9,]*\.?[0-9]*)/', $line, $m)) {
            return (float) str_replace(',', '', $m[1]);
        }

        return null;
    }
}
