<?php

namespace App\Services\BankEmailParsers\Contracts;

interface BankEmailParserInterface
{
    public function key(): string;

    public function bankName(): string;

    public function supports(string $sender, string $subject, string $body): bool;

    /**
     * Parse a bank notification email into a normalized transaction array.
     *
     * Returns null on hard parser failure (caller should mark message failed).
     * Returns array with at least: transaction_type, amount, currency, transaction_datetime.
     */
    public function parse(string $sender, string $subject, string $body, ?\DateTimeInterface $receivedAt = null): ?array;
}
