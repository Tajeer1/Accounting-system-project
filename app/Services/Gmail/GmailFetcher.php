<?php

namespace App\Services\Gmail;

use App\Models\BankEmailMessage;
use App\Models\GmailAccount;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GmailFetcher
{
    public function __construct(private readonly GoogleClientFactory $clientFactory)
    {
    }

    /**
     * Fetch unread bank emails from Gmail and store raw rows.
     * Returns count of newly stored messages.
     */
    public function fetchForAccount(GmailAccount $account, ?string $query = null, int $maxResults = 50): int
    {
        $client = $this->clientFactory->makeAuthenticatedClient($account);
        $gmail = new Gmail($client);

        $query ??= $this->defaultQuery($account);

        $listResponse = $gmail->users_messages->listUsersMessages('me', [
            'q' => $query,
            'maxResults' => $maxResults,
        ]);

        $stored = 0;

        foreach (($listResponse->getMessages() ?? []) as $messageStub) {
            $messageId = $messageStub->getId();

            if (BankEmailMessage::where('gmail_message_id', $messageId)->exists()) {
                continue;
            }

            try {
                $message = $gmail->users_messages->get('me', $messageId, ['format' => 'full']);
                $this->storeMessage($account, $message);
                $stored++;
            } catch (\Throwable $e) {
                Log::warning('Failed to fetch Gmail message', [
                    'message_id' => $messageId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $account->last_synced_at = now();
        $account->save();

        return $stored;
    }

    private function defaultQuery(GmailAccount $account): string
    {
        $sinceDays = 14;
        $afterTs = now()->subDays($sinceDays)->timestamp;

        return sprintf(
            '(from:(alrajhibank.com.sa) OR from:(alahli.com) OR from:(riyadbank.com) OR from:(snb.com) OR from:(bankalbilad.com) OR from:(alinma.com) OR subject:(خصم OR إيداع OR عملية OR purchase OR debit OR credit)) after:%d',
            $afterTs
        );
    }

    private function storeMessage(GmailAccount $account, Message $message): BankEmailMessage
    {
        $payload = $message->getPayload();
        $headers = $this->headersToArray($payload?->getHeaders() ?? []);

        [$fromEmail, $fromName] = $this->parseFrom($headers['from'] ?? '');
        $bodyPlain = $this->extractBody($payload, 'text/plain');
        $bodyHtml = $this->extractBody($payload, 'text/html');

        $internalDate = $message->getInternalDate();
        $receivedAt = $internalDate
            ? Carbon::createFromTimestampMs((int) $internalDate)
            : now();

        return BankEmailMessage::create([
            'gmail_account_id' => $account->id,
            'gmail_message_id' => $message->getId(),
            'thread_id' => $message->getThreadId(),
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'subject' => $headers['subject'] ?? null,
            'snippet' => $message->getSnippet(),
            'body_plain' => $bodyPlain,
            'body_html' => $bodyHtml,
            'received_at' => $receivedAt,
            'status' => 'pending',
        ]);
    }

    private function headersToArray(array $headers): array
    {
        $out = [];
        foreach ($headers as $h) {
            $out[strtolower($h->getName())] = $h->getValue();
        }
        return $out;
    }

    private function parseFrom(string $from): array
    {
        if (preg_match('/^(.*?)<(.+?)>\s*$/', $from, $m)) {
            return [trim($m[2]), trim($m[1], " \t\n\r\0\x0B\"")];
        }
        return [trim($from), null];
    }

    private function extractBody($part, string $mimeType): ?string
    {
        if (! $part) {
            return null;
        }

        if ($part->getMimeType() === $mimeType && $part->getBody()?->getData()) {
            return $this->decodeBody($part->getBody()->getData());
        }

        foreach (($part->getParts() ?? []) as $sub) {
            $found = $this->extractBody($sub, $mimeType);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    private function decodeBody(string $data): string
    {
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        return $decoded !== false ? $decoded : '';
    }
}
