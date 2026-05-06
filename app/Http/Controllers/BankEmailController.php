<?php

namespace App\Http\Controllers;

use App\Jobs\FetchBankEmailsJob;
use App\Models\BankEmailMessage;
use App\Models\GmailAccount;
use App\Services\BankEmailParsers\BankEmailParserManager;
use App\Services\Gmail\GmailFetcher;
use Illuminate\Http\Request;

class BankEmailController extends Controller
{
    public function index(Request $request)
    {
        $accounts = GmailAccount::orderByDesc('is_active')->orderBy('email')->get();

        $query = BankEmailMessage::with('gmailAccount', 'bankAccount')
            ->latest('received_at');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($accountId = $request->get('gmail_account_id')) {
            $query->where('gmail_account_id', $accountId);
        }
        if ($search = $request->get('q')) {
            $query->where(function ($w) use ($search) {
                $w->where('subject', 'like', "%{$search}%")
                  ->orWhere('from_email', 'like', "%{$search}%")
                  ->orWhere('snippet', 'like', "%{$search}%");
            });
        }

        $messages = $query->paginate(20)->withQueryString();

        return view('bank-emails.index', compact('accounts', 'messages'));
    }

    public function show(BankEmailMessage $bankEmail)
    {
        $bankEmail->load('gmailAccount', 'bankAccount', 'transactions.bankAccount');
        return view('bank-emails.show', ['message' => $bankEmail]);
    }

    public function fetch(Request $request, GmailFetcher $fetcher)
    {
        $accountId = $request->get('gmail_account_id');
        $accounts = $accountId
            ? GmailAccount::where('id', $accountId)->where('is_active', true)->get()
            : GmailAccount::where('is_active', true)->get();

        if ($accounts->isEmpty()) {
            return back()->with('error', 'لا يوجد حساب Gmail مفعّل. اربط حساب Gmail أولاً.');
        }

        $total = 0;
        foreach ($accounts as $account) {
            try {
                $total += $fetcher->fetchForAccount($account);
            } catch (\Throwable $e) {
                return back()->with('error', 'فشل جلب الإيميلات: '.$e->getMessage());
            }
        }

        return back()->with('success', "تم جلب {$total} إيميل جديد.");
    }

    public function dispatchFetch()
    {
        FetchBankEmailsJob::dispatch();
        return back()->with('success', 'تم وضع مهمة الجلب في الطابور.');
    }

    public function parse(BankEmailMessage $bankEmail, BankEmailParserManager $manager)
    {
        $count = $manager->processMessage($bankEmail);
        return back()->with('success', "تم استخراج {$count} عملية من الإيميل.");
    }

    public function ignore(BankEmailMessage $bankEmail)
    {
        $bankEmail->update(['status' => 'ignored']);
        return back()->with('success', 'تم تجاهل الإيميل.');
    }
}
