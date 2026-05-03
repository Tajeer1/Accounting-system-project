<?php

namespace Tests\Unit;

use App\Services\BankEmailParsers\BankMuscatParser;
use PHPUnit\Framework\TestCase;

class BankMuscatParserTest extends TestCase
{
    private BankMuscatParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new BankMuscatParser();
    }

    public function test_supports_bank_muscat_emails(): void
    {
        $this->assertTrue(
            $this->parser->supports('alerts@bankmuscat.com', 'Card Transaction Alert', 'Bank Muscat')
        );
    }

    public function test_does_not_support_unrelated_emails(): void
    {
        $this->assertFalse(
            $this->parser->supports('noreply@example.com', 'Hello', 'Just a regular email')
        );
    }

    public function test_parses_sample_debit_card_email(): void
    {
        $body = <<<EOT
Dear Customer,
Your Debit card number 4837**** ****4922 has been utilised as follows:

Account number : xxxx0022
Description : 103932-1 OF 1 AL M
Amount : OMR 0.5
Date/Time : 03 MAY 26 09:08
Transaction Country : Oman

Kind Regards,
Bank Muscat
EOT;

        $result = $this->parser->parse('alerts@bankmuscat.com', 'Card Transaction Alert', $body);

        $this->assertNotNull($result);
        $this->assertSame('Bank Muscat', $result['bank_name']);
        $this->assertSame('debit', $result['transaction_type']);
        $this->assertSame('4837**** ****4922', $result['masked_card_number']);
        $this->assertSame('xxxx0022', $result['masked_account_number']);
        $this->assertSame('103932-1 OF 1 AL M', $result['description']);
        $this->assertSame(0.5, $result['amount']);
        $this->assertSame('OMR', $result['currency']);
        $this->assertSame('2026-05-03 09:08:00', $result['transaction_datetime']);
        $this->assertSame('Oman', $result['transaction_country']);
        $this->assertNull($result['balance_after']);
    }

    public function test_returns_null_when_amount_is_missing(): void
    {
        $body = <<<EOT
Dear Customer,
Account number : xxxx0022
Description : missing amount line
Date/Time : 03 MAY 26 09:08
EOT;

        $this->assertNull($this->parser->parse('alerts@bankmuscat.com', 'Alert', $body));
    }

    public function test_detects_credit_keyword(): void
    {
        $body = <<<EOT
Dear Customer,
Your account has been credited with the following:

Account number : xxxx0022
Description : Salary transfer
Amount : OMR 1500.000
Date/Time : 25 APR 26 14:30

Bank Muscat
EOT;

        $result = $this->parser->parse('alerts@bankmuscat.com', 'Salary Credit', $body);

        $this->assertNotNull($result);
        $this->assertSame('credit', $result['transaction_type']);
        $this->assertSame(1500.0, $result['amount']);
    }

    public function test_parses_amount_with_thousands_separator(): void
    {
        $body = <<<EOT
Account number : xxxx0022
Description : Big purchase
Amount : OMR 1,234.567
Date/Time : 03 MAY 26 09:08
EOT;

        $result = $this->parser->parse('alerts@bankmuscat.com', 'Alert', "Debit card\n" . $body);

        $this->assertNotNull($result);
        $this->assertSame(1234.567, $result['amount']);
    }

    public function test_extracts_balance_when_present(): void
    {
        $body = <<<EOT
Your Debit card number 4837**** ****4922 has been utilised:
Account number : xxxx0022
Description : Test
Amount : OMR 10.000
Date/Time : 03 MAY 26 09:08
Available Balance : OMR 500.250
EOT;

        $result = $this->parser->parse('alerts@bankmuscat.com', 'Alert', $body);

        $this->assertNotNull($result);
        $this->assertSame(500.25, $result['balance_after']);
    }
}
