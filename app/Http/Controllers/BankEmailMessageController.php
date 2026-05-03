<?php

namespace App\Http\Controllers;

use App\Models\BankEmailMessage;
use Illuminate\Http\Request;

class BankEmailMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = BankEmailMessage::with('integration', 'transaction')->latest('received_at');

        if ($status = $request->string('status')->toString()) {
            $query->where('processing_status', $status);
        }
        if ($integrationId = $request->integer('integration_id')) {
            $query->where('integration_id', $integrationId);
        }

        $messages = $query->paginate(25)->withQueryString();

        $counts = [
            'pending' => BankEmailMessage::where('processing_status', 'pending')->count(),
            'processed' => BankEmailMessage::where('processing_status', 'processed')->count(),
            'failed' => BankEmailMessage::where('processing_status', 'failed')->count(),
            'duplicate' => BankEmailMessage::where('processing_status', 'duplicate')->count(),
        ];

        return view('bank-emails.messages.index', compact('messages', 'counts'));
    }

    public function show(BankEmailMessage $message)
    {
        $message->load('integration', 'transaction');
        return view('bank-emails.messages.show', ['message' => $message]);
    }
}
