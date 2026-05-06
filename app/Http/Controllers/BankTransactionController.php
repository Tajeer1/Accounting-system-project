<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Category;
use App\Models\Project;
use App\Models\Purchase;
use App\Services\AccountingService;
use Illuminate\Http\Request;

class BankTransactionController extends Controller
{
    public function __construct(protected AccountingService $accounting) {}

    public function index(Request $request)
    {
        $query = BankTransaction::with('bankAccount', 'category', 'project', 'purchase', 'emailMessage')
            ->latest('transaction_date');

        $status = $request->get('status', 'pending_review');
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($accountId = $request->get('bank_account_id')) {
            $query->where('bank_account_id', $accountId);
        }
        if ($direction = $request->get('direction')) {
            $query->where('direction', $direction);
        }

        $transactions = $query->paginate(20)->withQueryString();
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'pending' => BankTransaction::where('status', 'pending_review')->count(),
            'approved' => BankTransaction::where('status', 'approved')->count(),
            'linked' => BankTransaction::where('status', 'linked')->count(),
            'rejected' => BankTransaction::where('status', 'rejected')->count(),
        ];

        return view('bank-transactions.index', compact('transactions', 'bankAccounts', 'stats', 'status'));
    }

    public function show(BankTransaction $bankTransaction)
    {
        $bankTransaction->load('bankAccount', 'category', 'project', 'purchase', 'emailMessage');
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        return view('bank-transactions.show', [
            'transaction' => $bankTransaction,
            'bankAccounts' => $bankAccounts,
            'categories' => $categories,
            'projects' => $projects,
        ]);
    }

    public function update(Request $request, BankTransaction $bankTransaction)
    {
        $data = $request->validate([
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'merchant' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $bankTransaction->update($data);

        return back()->with('success', 'تم تحديث العملية.');
    }

    public function reject(BankTransaction $bankTransaction)
    {
        $bankTransaction->update(['status' => 'rejected']);
        return back()->with('success', 'تم رفض العملية.');
    }

    public function approve(BankTransaction $bankTransaction)
    {
        $bankTransaction->update(['status' => 'approved']);
        return back()->with('success', 'تم اعتماد العملية.');
    }

    /**
     * Convert this transaction into a Purchase record (for debit transactions).
     */
    public function convertToPurchase(Request $request, BankTransaction $bankTransaction)
    {
        if ($bankTransaction->direction !== 'debit') {
            return back()->with('error', 'فقط عمليات الخصم يمكن تحويلها إلى مشترى.');
        }
        if ($bankTransaction->purchase_id) {
            return back()->with('error', 'العملية مرتبطة بمشترى مسبقاً.');
        }
        if (! $bankTransaction->bank_account_id) {
            return back()->with('error', 'يجب تحديد الحساب البنكي أولاً.');
        }

        $supplier = $request->input('supplier_name')
            ?: ($bankTransaction->merchant ?: 'مزود غير محدد');

        $purchase = Purchase::create([
            'number' => Purchase::generateNumber(),
            'purchase_date' => $bankTransaction->transaction_date,
            'supplier_name' => $supplier,
            'amount' => $bankTransaction->amount,
            'category_id' => $bankTransaction->category_id,
            'bank_account_id' => $bankTransaction->bank_account_id,
            'project_id' => $bankTransaction->project_id,
            'description' => trim(($bankTransaction->notes ?? '') . "\nمصدر: إيميل بنكي رقم #{$bankTransaction->id}"),
            'status' => 'paid',
        ]);

        $this->accounting->createEntryForPurchase($purchase);

        $bankTransaction->update([
            'status' => 'linked',
            'purchase_id' => $purchase->id,
        ]);

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'تم إنشاء عملية شراء مرتبطة بالخصم البنكي.');
    }
}
