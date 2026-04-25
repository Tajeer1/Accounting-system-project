<?php

namespace App\Http\Controllers;

use App\Models\AccountTransfer;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankAccountController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::orderBy('name')->get();
        $total = $accounts->sum('current_balance');

        return view('bank-accounts.index', compact('accounts', 'total'));
    }

    public function create()
    {
        return view('bank-accounts.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['current_balance'] = $data['opening_balance'];
        $data['is_active'] = $request->boolean('is_active', true);

        BankAccount::create($data);

        return redirect()->route('bank-accounts.index')->with('success', 'تم إضافة الحساب');
    }

    public function show(BankAccount $bankAccount)
    {
        $bankAccount->recalculateBalance();

        $purchases = $bankAccount->purchases()->latest('purchase_date')->take(10)->get()
            ->map(fn ($p) => (object) [
                'date' => $p->purchase_date,
                'description' => 'مشتريات — ' . $p->supplier_name,
                'reference' => $p->number,
                'in' => 0,
                'out' => $p->amount,
            ]);

        $invoicesIn = $bankAccount->invoices()->where('type', 'sales')->where('status', 'paid')
            ->latest('issue_date')->take(10)->get()
            ->map(fn ($i) => (object) [
                'date' => $i->issue_date,
                'description' => 'فاتورة مبيعات — ' . $i->party_name,
                'reference' => $i->number,
                'in' => $i->amount,
                'out' => 0,
            ]);

        $invoicesOut = $bankAccount->invoices()->where('type', 'purchase')->where('status', 'paid')
            ->latest('issue_date')->take(10)->get()
            ->map(fn ($i) => (object) [
                'date' => $i->issue_date,
                'description' => 'فاتورة مشتريات — ' . $i->party_name,
                'reference' => $i->number,
                'in' => 0,
                'out' => $i->amount,
            ]);

        $transactions = collect()
            ->concat($purchases)
            ->concat($invoicesIn)
            ->concat($invoicesOut)
            ->sortByDesc('date')
            ->values();

        return view('bank-accounts.show', compact('bankAccount', 'transactions'));
    }

    public function edit(BankAccount $bankAccount)
    {
        return view('bank-accounts.edit', ['account' => $bankAccount]);
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $data = $this->validateData($request, $bankAccount->id);
        $data['is_active'] = $request->boolean('is_active', true);
        $bankAccount->update($data);
        $bankAccount->recalculateBalance();

        return redirect()->route('bank-accounts.index')->with('success', 'تم تحديث الحساب');
    }

    public function destroy(BankAccount $bankAccount)
    {
        if ($bankAccount->purchases()->exists() || $bankAccount->invoices()->exists()) {
            return back()->with('error', 'لا يمكن حذف حساب مرتبط بعمليات');
        }

        $bankAccount->delete();
        return redirect()->route('bank-accounts.index')->with('success', 'تم حذف الحساب');
    }

    public function transferCreate()
    {
        $accounts = BankAccount::where('is_active', true)->orderBy('name')->get();
        return view('bank-accounts.transfer', compact('accounts'));
    }

    public function transferStore(Request $request)
    {
        $data = $request->validate([
            'from_account_id' => ['required', 'different:to_account_id', 'exists:bank_accounts,id'],
            'to_account_id' => ['required', 'exists:bank_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transfer_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            AccountTransfer::create(array_merge($data, [
                'number' => AccountTransfer::generateNumber(),
            ]));

            BankAccount::find($data['from_account_id'])->recalculateBalance();
            BankAccount::find($data['to_account_id'])->recalculateBalance();
        });

        return redirect()->route('bank-accounts.index')->with('success', 'تم تنفيذ التحويل');
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:bank,cash,other'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
