<?php

namespace App\Services\BankEmailParsers;

class ParsedTransaction
{
    public function __construct(
        public string $direction,
        public float $amount,
        public string $currency,
        public string $transactionDate,
        public ?string $merchant = null,
        public ?string $reference = null,
        public ?string $cardLast4 = null,
        public ?float $balanceAfter = null,
        public ?string $rawMatch = null,
    ) {}

    public function toArray(): array
    {
        return [
            'direction' => $this->direction,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'transaction_date' => $this->transactionDate,
            'merchant' => $this->merchant,
            'reference' => $this->reference,
            'card_last4' => $this->cardLast4,
            'balance_after' => $this->balanceAfter,
            'raw_match' => $this->rawMatch,
        ];
    }
}
