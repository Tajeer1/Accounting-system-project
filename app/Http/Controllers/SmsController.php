<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Setting;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function __construct(protected SmsService $sms) {}

    public function index()
    {
        return view('sms.index', [
            'configured' => $this->sms->isConfigured(),
            'recentInvoices' => Invoice::latest('issue_date')->take(10)->get(),
        ]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'to' => ['required', 'string', 'max:20'],
            'body' => ['required', 'string', 'max:1600'],
        ]);

        $result = $this->sms->send($data['to'], $data['body']);

        if ($result['ok']) {
            return back()->with('success', 'تم إرسال الرسالة بنجاح (SID: ' . $result['sid'] . ')');
        }

        return back()->with('error', $this->humanize($result['message'] ?? 'خطأ غير معروف'))->withInput();
    }

    protected function humanize(string $error): string
    {
        if (str_contains($error, 'unverified')) {
            return 'الرقم غير مُتحقَّق منه في حساب Twilio Trial. تحقّق منه أولاً عبر: https://console.twilio.com/us1/develop/phone-numbers/manage/verified';
        }
        if (str_contains($error, 'Authenticate')) {
            return 'بيانات Twilio خاطئة. تأكد من TWILIO_SID و TWILIO_TOKEN في .env';
        }
        if (str_contains($error, 'not a valid phone number')) {
            return 'رقم الهاتف غير صحيح. استخدم صيغة E.164 مثل: +96898765432';
        }
        if (str_contains($error, 'is not a Twilio phone number') || str_contains($error, 'From phone number')) {
            return 'رقم المُرسل (TWILIO_FROM) غير صحيح أو لا يخص حسابك';
        }
        return 'فشل الإرسال: ' . $error;
    }

    public function sendInvoiceNotification(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'to' => ['required', 'string', 'max:20'],
        ]);

        $companyName = Setting::get('company_name', 'Event Plus');
        $amount = money($invoice->amount);
        $body = "{$companyName}\nفاتورة رقم {$invoice->number}\nالعميل: {$invoice->party_name}\nالمبلغ: {$amount}\nالحالة: {$invoice->statusLabel()}";

        $result = $this->sms->send($data['to'], $body);

        if ($result['ok']) {
            return back()->with('success', 'تم إرسال إشعار الفاتورة عبر SMS');
        }

        return back()->with('error', $this->humanize($result['message'] ?? 'خطأ غير معروف'));
    }
}
