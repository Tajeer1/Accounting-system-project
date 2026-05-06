<?php

namespace App\Http\Controllers;

use App\Models\GmailAccount;
use App\Services\Gmail\GoogleClientFactory;
use Google\Service\Oauth2;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GmailAuthController extends Controller
{
    public function __construct(private readonly GoogleClientFactory $clientFactory)
    {
    }

    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()
                ->route('bank-emails.index')
                ->with('error', 'لم يتم ضبط بيانات Google OAuth في ملف .env');
        }

        $client = $this->clientFactory->makeBaseClient();
        $url = $client->createAuthUrl();

        return redirect()->away($url);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('bank-emails.index')
                ->with('error', 'تم إلغاء الربط: '.$request->string('error'));
        }

        $code = $request->string('code')->toString();
        if ($code === '') {
            return redirect()->route('bank-emails.index')
                ->with('error', 'لم يتم استلام رمز التفويض من Google.');
        }

        $client = $this->clientFactory->makeBaseClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            return redirect()->route('bank-emails.index')
                ->with('error', 'فشل تبادل رمز التفويض: '.$token['error']);
        }

        $client->setAccessToken($token);
        $oauth = new Oauth2($client);
        $userInfo = $oauth->userinfo->get();
        $email = $userInfo->getEmail();

        $account = GmailAccount::updateOrCreate(
            ['email' => $email],
            [
                'label' => $userInfo->getName(),
                'access_token' => $token['access_token'] ?? null,
                'refresh_token' => $token['refresh_token'] ?? null,
                'token_expires_at' => isset($token['expires_in'])
                    ? now()->addSeconds((int) $token['expires_in'])
                    : null,
                'is_active' => true,
            ]
        );

        return redirect()->route('bank-emails.index')
            ->with('success', "تم ربط حساب Gmail: {$account->email}");
    }

    public function disconnect(GmailAccount $gmailAccount): RedirectResponse
    {
        $gmailAccount->update([
            'is_active' => false,
            'access_token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
        ]);

        return redirect()->route('bank-emails.index')
            ->with('success', "تم فصل حساب Gmail: {$gmailAccount->email}");
    }
}
