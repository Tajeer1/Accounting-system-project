<?php

namespace App\Services\BankEmailParsers;

use App\Models\BankEmailMessage;

/**
 * Generic fallback parser. Extracts amount, currency, and direction
 * heuristically from Arabic / English bank notification emails.
 * Bank-specific parsers should be preferred when available.
 */
class GenericBankParser implements BankEmailParserInterface
{
    public function key(): string
    {
        return 'generic';
    }

    public function matches(BankEmailMessage $message): bool
    {
        return true;
    }

    public function parse(BankEmailMessage $message): array
    {
        $text = trim(($message->body_plain ?? '') . "\n" . strip_tags($message->body_html ?? ''));
        if ($text === '') {
            $text = (string) $message->snippet;
        }

        $direction = $this->detectDirection($text, (string) $message->subject);
        $amount = $this->extractAmount($text);
        $currency = $this->extractCurrency($text) ?? 'SAR';
        $cardLast4 = $this->extractCardLast4($text);
        $merchant = $this->extractMerchant($text);
        $reference = $this->extractReference($text);
        $balanceAfter = $this->extractBalance($text);

        if ($amount === null) {
            return [];
        }

        return [
            new ParsedTransaction(
                direction: $direction,
                amount: $amount,
                currency: $currency,
                transactionDate: optional($message->received_at)->toDateString() ?? now()->toDateString(),
                merchant: $merchant,
                reference: $reference,
                cardLast4: $cardLast4,
                balanceAfter: $balanceAfter,
                rawMatch: mb_substr($text, 0, 500),
            ),
        ];
    }

    private function detectDirection(string $text, string $subject): string
    {
        $haystack = mb_strtolower($subject . ' ' . $text);
        $debitKeywords = ['خصم', 'مدين', 'شراء', 'سحب', 'دفع', 'debit', 'purchase', 'withdraw', 'paid'];
        $creditKeywords = ['إيداع', 'ايداع', 'دائن', 'استلام', 'تحويل وارد', 'credit', 'deposit', 'received'];

        foreach ($debitKeywords as $kw) {
            if (mb_stripos($haystack, $kw) !== false) {
                return 'debit';
            }
        }
        foreach ($creditKeywords as $kw) {
            if (mb_stripos($haystack, $kw) !== false) {
                return 'credit';
            }
        }

        return 'debit';
    }

    private function extractAmount(string $text): ?float
    {
        if (preg_match('/(\d{1,3}(?:[,،]\d{3})*(?:\.\d{1,3})?)\s*(?:SAR|SR|ر\.?س|ريال|KWD|د\.?ك|AED|درهم|USD|\$)/iu', $text, $m)) {
            return (float) str_replace([',', '،'], '', $m[1]);
        }

        if (preg_match('/(?:SAR|SR|ر\.?س|ريال|KWD|د\.?ك|AED|درهم|USD|\$)\s*(\d{1,3}(?:[,،]\d{3})*(?:\.\d{1,3})?)/iu', $text, $m)) {
            return (float) str_replace([',', '،'], '', $m[1]);
        }

        if (preg_match('/(?:المبلغ|Amount)[:\s]+(\d{1,3}(?:[,،]\d{3})*(?:\.\d{1,3})?)/iu', $text, $m)) {
            return (float) str_replace([',', '،'], '', $m[1]);
        }

        return null;
    }

    private function extractCurrency(string $text): ?string
    {
        $map = [
            '/\bSAR\b|\bSR\b|ر\.?س|ريال/u' => 'SAR',
            '/\bKWD\b|د\.?ك/u' => 'KWD',
            '/\bAED\b|درهم/u' => 'AED',
            '/\bUSD\b|\$/u' => 'USD',
            '/\bEUR\b|€/u' => 'EUR',
        ];

        foreach ($map as $pattern => $code) {
            if (preg_match($pattern, $text)) {
                return $code;
            }
        }

        return null;
    }

    private function extractCardLast4(string $text): ?string
    {
        if (preg_match('/(?:\*+|x{2,}|X{2,}|بطاقة[^\d]{0,20})(\d{4})/u', $text, $m)) {
            return $m[1];
        }
        return null;
    }

    private function extractMerchant(string $text): ?string
    {
        if (preg_match('/(?:لدى|في|من|عند|at|merchant[:\s])\s+([\p{L}0-9 \-_.&]+)/u', $text, $m)) {
            $name = trim($m[1]);
            return mb_substr($name, 0, 80) ?: null;
        }
        return null;
    }

    private function extractReference(string $text): ?string
    {
        if (preg_match('/(?:رقم العملية|Reference|REF|Auth)[:\s]*([A-Z0-9\-]{4,})/iu', $text, $m)) {
            return $m[1];
        }
        return null;
    }

    private function extractBalance(string $text): ?float
    {
        if (preg_match('/(?:الرصيد|Balance)[:\s]*(\d{1,3}(?:[,،]\d{3})*(?:\.\d{1,3})?)/iu', $text, $m)) {
            return (float) str_replace([',', '،'], '', $m[1]);
        }
        return null;
    }
}
