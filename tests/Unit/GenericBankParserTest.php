<?php

namespace Tests\Unit;

use App\Services\BankEmailParsers\GenericBankParser;
use PHPUnit\Framework\TestCase;

class GenericBankParserTest extends TestCase
{
    public function test_supports_anything_as_fallback(): void
    {
        $parser = new GenericBankParser();
        $this->assertTrue($parser->supports('whatever@example.com', 'subject', 'body'));
    }

    public function test_extracts_amount_with_currency_prefix(): void
    {
        $parser = new GenericBankParser();
        $result = $parser->parse(
            'bank@example.com',
            'Account debited',
            "Your account has been debited.\nAmount : USD 250.50\nNarration : Coffee"
        );

        $this->assertNotNull($result);
        $this->assertSame(250.5, $result['amount']);
        $this->assertSame('USD', $result['currency']);
        $this->assertSame('debit', $result['transaction_type']);
    }

    public function test_returns_null_when_no_amount_found(): void
    {
        $parser = new GenericBankParser();
        $result = $parser->parse('bank@example.com', 'random', 'no money values here at all');

        $this->assertNull($result);
    }
}
