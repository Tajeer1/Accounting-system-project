<?php

namespace App\Services\BankEmailParsers;

use App\Services\BankEmailParsers\Contracts\BankEmailParserInterface;

abstract class AbstractBankEmailParser implements BankEmailParserInterface
{
    protected const DEBIT_KEYWORDS = [
        'debit card', 'utilised', 'utilized', 'debited', 'withdrawal',
        'purchase', 'deducted', 'pos transaction', 'cash withdrawal',
        'مدين', 'خصم', 'سحب', 'مشتريات',
    ];

    protected const CREDIT_KEYWORDS = [
        'credited', 'deposit', 'received', 'salary', 'transfer in',
        'amount received', 'دائن', 'إيداع', 'استلام', 'راتب',
    ];

    protected function detectTransactionType(string $haystack): string
    {
        $lower = mb_strtolower($haystack);

        foreach (self::DEBIT_KEYWORDS as $kw) {
            if (str_contains($lower, mb_strtolower($kw))) {
                return 'debit';
            }
        }

        foreach (self::CREDIT_KEYWORDS as $kw) {
            if (str_contains($lower, mb_strtolower($kw))) {
                return 'credit';
            }
        }

        return 'unknown';
    }

    protected function normalizeBody(string $body): string
    {
        $body = preg_replace('/\r\n?/', "\n", $body);
        $body = preg_replace('/\xc2\xa0/', ' ', $body);
        return trim($body);
    }

    protected function captureAfterLabel(string $body, string $label): ?string
    {
        $pattern = '/' . preg_quote($label, '/') . '\s*[:：]\s*(.+)$/mi';
        if (preg_match($pattern, $body, $m)) {
            return trim($m[1]);
        }
        return null;
    }
}
