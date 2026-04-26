<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class SmsService
{
    protected ?Client $client = null;
    protected ?string $from = null;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->from = config('services.twilio.from');

        if ($sid && $token) {
            $this->client = new Client($sid, $token);
        }
    }

    public function isConfigured(): bool
    {
        return $this->client !== null && ! empty($this->from);
    }

    /**
     * Send an SMS via Twilio.
     *
     * @return array{ok: bool, sid?: string, message?: string}
     */
    public function send(string $to, string $body): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'Twilio غير مهيأ — أضف TWILIO_SID و TWILIO_TOKEN و TWILIO_FROM في .env',
            ];
        }

        $to = $this->normalizeNumber($to);

        try {
            $message = $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $body,
            ]);

            Log::info('SMS sent', ['to' => $to, 'sid' => $message->sid]);

            return ['ok' => true, 'sid' => $message->sid];
        } catch (TwilioException $e) {
            Log::error('Twilio SMS failed', ['to' => $to, 'error' => $e->getMessage()]);
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Ensure number is in E.164 format (e.g. +96898765432).
     */
    protected function normalizeNumber(string $number): string
    {
        $number = preg_replace('/[^\d+]/', '', $number);
        if (! str_starts_with($number, '+')) {
            // Default to Oman country code if no + provided
            $number = '+968' . ltrim($number, '0');
        }
        return $number;
    }
}
