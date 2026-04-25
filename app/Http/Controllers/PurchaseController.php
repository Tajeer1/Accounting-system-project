<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Purchase;
use App\Services\AccountingService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(protected AccountingService $accounting) {}

    public function index(Request $request)
    {
        $query = Purchase::with('category', 'bankAccount', 'project')->latest('purchase_date');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($projectId = $request->get('project_id')) {
            $query->where('project_id', $projectId);
        }
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%$search%")
                  ->orWhere('supplier_name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        $purchases = $query->paginate(15)->withQueryString();
        $projects = Project::orderBy('name')->get();
        $total = Purchase::where('status', 'paid')->sum('amount');
        $monthTotal = Purchase::where('status', 'paid')
            ->whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year)
            ->sum('amount');

        return view('purchases.index', compact('purchases', 'projects', 'total', 'monthTotal'));
    }

    public function create()
    {
        return view('purchases.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['number'] = Purchase::generateNumber();

        $purchase = Purchase::create($data);
        $this->accounting->createEntryForPurchase($purchase);

        return redirect()->route('purchases.show', $purchase)->with('success', 'تم إضافة عملية الشراء');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('category', 'bankAccount', 'project', 'invoice', 'journalEntry.lines.account');
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        return view('purchases.edit', array_merge($this->formData(), ['purchase' => $purchase]));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $data = $this->validateData($request);
        $purchase->update($data);
        $this->accounting->createEntryForPurchase($purchase);

        return redirect()->route('purchases.show', $purchase)->with('success', 'تم تحديث العملية');
    }

    public function destroy(Purchase $purchase)
    {
        $bankAccount = $purchase->bankAccount;
        $purchase->journalEntry()?->delete();
        $purchase->delete();
        $bankAccount?->recalculateBalance();

        return redirect()->route('purchases.index')->with('success', 'تم حذف العملية');
    }

    protected function formData(): array
    {
        return [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('name')->get(),
            'projects' => Project::orderBy('name')->get(),
            'invoices' => Invoice::where('type', 'purchase')->latest('issue_date')->get(),
        ];
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'purchase_date' => ['required', 'date'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,paid,cancelled'],
        ]);
    }
}
