<?php

namespace App\Services\BankEmailParsers;

use App\Models\BankEmailMessage;

interface BankEmailParserInterface
{
    /**
     * Unique key identifying the bank (e.g. 'alrajhi', 'snb', 'boubyan').
     */
    public function key(): string;

    /**
     * Whether this parser can handle the given email.
     */
    public function matches(BankEmailMessage $message): bool;

    /**
     * Parse the email into one or more transactions.
     *
     * @return ParsedTransaction[]
     */
    public function parse(BankEmailMessage $message): array;
}
