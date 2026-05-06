<?php

namespace App\Services\Gmail;

use App\Models\GmailAccount;
use Google\Client as GoogleClient;
use Google\Service\Gmail;

class GoogleClientFactory
{
    public const SCOPES = [
        Gmail::GMAIL_READONLY,
    ];

    public function makeBaseClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setClientId((string) config('services.google.client_id'));
        $client->setClientSecret((string) config('services.google.client_secret'));
        $client->setRedirectUri((string) config('services.google.redirect_uri'));
        $client->setScopes(self::SCOPES);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setIncludeGrantedScopes(true);

        return $client;
    }

    public function makeAuthenticatedClient(GmailAccount $account): GoogleClient
    {
        $client = $this->makeBaseClient();

        $token = [
            'access_token' => $account->access_token,
            'refresh_token' => $account->refresh_token,
            'expires_in' => $account->token_expires_at
                ? max(0, $account->token_expires_at->diffInSeconds(now(), false) * -1)
                : 0,
            'created' => $account->updated_at?->getTimestamp() ?? time(),
        ];

        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired() && $account->refresh_token) {
            $refreshed = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);

            if (isset($refreshed['access_token'])) {
                $account->access_token = $refreshed['access_token'];
                if (isset($refreshed['expires_in'])) {
                    $account->token_expires_at = now()->addSeconds((int) $refreshed['expires_in']);
                }
                if (isset($refreshed['refresh_token'])) {
                    $account->refresh_token = $refreshed['refresh_token'];
                }
                $account->save();
            }
        }

        return $client;
    }
}
