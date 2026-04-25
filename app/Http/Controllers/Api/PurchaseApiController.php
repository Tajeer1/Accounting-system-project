<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Services\AccountingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseApiController extends Controller
{
    public function __construct(protected AccountingService $accounting) {}

    public function index(Request $request): JsonResponse
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
                $q->where('supplier_name', 'like', "%$search%")
                  ->orWhere('number', 'like', "%$search%");
            });
        }

        return response()->json($query->paginate(20)->through(fn ($p) => $this->transform($p)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'purchase_date' => ['required', 'date'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,paid,cancelled'],
        ]);

        $data['number'] = Purchase::generateNumber();
        $purchase = Purchase::create($data);
        $this->accounting->createEntryForPurchase($purchase);

        return response()->json($this->transform($purchase->load('category', 'bankAccount', 'project')), 201);
    }

    public function show(Purchase $purchase): JsonResponse
    {
        $purchase->load('category', 'bankAccount', 'project', 'invoice', 'journalEntry.lines.account');
        return response()->json([
            'purchase' => $this->transform($purchase),
            'journal_entry' => $purchase->journalEntry ? [
                'id' => $purchase->journalEntry->id,
                'number' => $purchase->journalEntry->number,
                'status' => $purchase->journalEntry->status,
                'total_debit' => (float) $purchase->journalEntry->total_debit,
                'lines' => $purchase->journalEntry->lines->map(fn ($l) => [
                    'account' => $l->account->name ?? '—',
                    'code' => $l->account->code ?? '',
                    'debit' => (float) $l->debit,
                    'credit' => (float) $l->credit,
                ]),
            ] : null,
        ]);
    }

    public function update(Request $request, Purchase $purchase): JsonResponse
    {
        $data = $request->validate([
            'purchase_date' => ['required', 'date'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,paid,cancelled'],
        ]);

        $purchase->update($data);
        $this->accounting->createEntryForPurchase($purchase);

        return response()->json($this->transform($purchase->fresh(['category', 'bankAccount', 'project'])));
    }

    public function destroy(Purchase $purchase): JsonResponse
    {
        $bankAccount = $purchase->bankAccount;
        $purchase->journalEntry()?->delete();
        $purchase->delete();
        $bankAccount?->recalculateBalance();

        return response()->json(['ok' => true]);
    }

    protected function transform(Purchase $p): array
    {
        return [
            'id' => $p->id,
            'number' => $p->number,
            'purchase_date' => $p->purchase_date->toDateString(),
            'supplier_name' => $p->supplier_name,
            'amount' => (float) $p->amount,
            'status' => $p->status,
            'status_label' => $p->statusLabel(),
            'description' => $p->description,
            'category' => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name, 'color' => $p->category->color] : null,
            'bank_account' => $p->bankAccount ? ['id' => $p->bankAccount->id, 'name' => $p->bankAccount->name] : null,
            'project' => $p->project ? ['id' => $p->project->id, 'name' => $p->project->name, 'code' => $p->project->code] : null,
            'category_id' => $p->category_id,
            'bank_account_id' => $p->bank_account_id,
            'project_id' => $p->project_id,
        ];
    }
}
