<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\AccountingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceApiController extends Controller
{
    public function __construct(protected AccountingService $accounting) {}

    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with('project', 'bankAccount')->latest('issue_date');

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('party_name', 'like', "%$search%")
                  ->orWhere('number', 'like', "%$search%");
            });
        }

        return response()->json([
            'data' => $query->paginate(20)->through(fn ($i) => $this->transform($i)),
            'totals' => [
                'sales' => (float) Invoice::where('type', 'sales')->sum('amount'),
                'purchase' => (float) Invoice::where('type', 'purchase')->sum('amount'),
                'paid' => (float) Invoice::where('status', 'paid')->sum('amount'),
                'unpaid' => (float) Invoice::whereIn('status', ['draft', 'sent', 'overdue'])->sum('amount'),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request);
        $data['number'] = Invoice::generateNumber($data['type']);
        $invoice = Invoice::create($data);
        $this->accounting->createEntryForInvoice($invoice);

        return response()->json($this->transform($invoice->load('project', 'bankAccount')), 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load('project', 'bankAccount', 'category', 'journalEntry.lines.account');
        return response()->json([
            'invoice' => $this->transform($invoice),
            'journal_entry' => $invoice->journalEntry ? [
                'id' => $invoice->journalEntry->id,
                'number' => $invoice->journalEntry->number,
                'status' => $invoice->journalEntry->status,
                'lines' => $invoice->journalEntry->lines->map(fn ($l) => [
                    'account' => $l->account->name ?? '—',
                    'code' => $l->account->code ?? '',
                    'debit' => (float) $l->debit,
                    'credit' => (float) $l->credit,
                ]),
            ] : null,
        ]);
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $data = $this->validateData($request);
        $invoice->update($data);
        $this->accounting->createEntryForInvoice($invoice);

        return response()->json($this->transform($invoice->fresh(['project', 'bankAccount'])));
    }

    public function markPaid(Invoice $invoice): JsonResponse
    {
        $invoice->update(['status' => 'paid']);
        $this->accounting->createEntryForInvoice($invoice);
        return response()->json($this->transform($invoice->fresh(['project', 'bankAccount'])));
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $bankAccount = $invoice->bankAccount;
        $invoice->journalEntry()?->delete();
        $invoice->delete();
        $bankAccount?->recalculateBalance();

        return response()->json(['ok' => true]);
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:sales,purchase'],
            'party_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', 'in:draft,sent,paid,overdue,cancelled'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
        ]);
    }

    protected function transform(Invoice $i): array
    {
        return [
            'id' => $i->id,
            'number' => $i->number,
            'type' => $i->type,
            'type_label' => $i->typeLabel(),
            'party_name' => $i->party_name,
            'amount' => (float) $i->amount,
            'issue_date' => $i->issue_date->toDateString(),
            'due_date' => $i->due_date?->toDateString(),
            'status' => $i->status,
            'status_label' => $i->statusLabel(),
            'description' => $i->description,
            'project' => $i->project ? ['id' => $i->project->id, 'name' => $i->project->name] : null,
            'bank_account' => $i->bankAccount ? ['id' => $i->bankAccount->id, 'name' => $i->bankAccount->name] : null,
            'project_id' => $i->project_id,
            'bank_account_id' => $i->bank_account_id,
            'category_id' => $i->category_id,
        ];
    }
}
