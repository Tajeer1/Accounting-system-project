<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Setting;
use App\Services\AccountingService;
use App\Services\PdfService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(protected AccountingService $accounting) {}

    public function index(Request $request)
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
                $q->where('number', 'like', "%$search%")
                  ->orWhere('party_name', 'like', "%$search%");
            });
        }

        $invoices = $query->paginate(15)->withQueryString();

        $totals = [
            'sales' => Invoice::where('type', 'sales')->sum('amount'),
            'purchase' => Invoice::where('type', 'purchase')->sum('amount'),
            'unpaid' => Invoice::whereIn('status', ['draft', 'sent', 'overdue'])->sum('amount'),
            'paid' => Invoice::where('status', 'paid')->sum('amount'),
        ];

        return view('invoices.index', compact('invoices', 'totals'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'sales');
        return view('invoices.create', array_merge($this->formData(), ['type' => $type]));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['number'] = Invoice::generateNumber($data['type']);

        $invoice = Invoice::create($data);
        $this->accounting->createEntryForInvoice($invoice);

        return redirect()->route('invoices.show', $invoice)->with('success', 'تم إضافة الفاتورة');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('project', 'bankAccount', 'category', 'journalEntry.lines.account', 'purchases');
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        return view('invoices.edit', array_merge($this->formData(), ['invoice' => $invoice]));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $this->validateData($request);
        $invoice->update($data);
        $this->accounting->createEntryForInvoice($invoice);

        return redirect()->route('invoices.show', $invoice)->with('success', 'تم تحديث الفاتورة');
    }

    public function destroy(Invoice $invoice)
    {
        $bankAccount = $invoice->bankAccount;
        $invoice->journalEntry()?->delete();
        $invoice->delete();
        $bankAccount?->recalculateBalance();

        return redirect()->route('invoices.index')->with('success', 'تم حذف الفاتورة');
    }

    public function markPaid(Invoice $invoice)
    {
        $invoice->update(['status' => 'paid']);
        $this->accounting->createEntryForInvoice($invoice);
        return back()->with('success', 'تم تحديث حالة الفاتورة إلى مدفوعة');
    }

    public function downloadPdf(Invoice $invoice, PdfService $pdfService)
    {
        $html = $this->renderInvoice($invoice);
        return $pdfService->download($html, 'invoice-' . $invoice->number);
    }

    public function viewPdf(Invoice $invoice, PdfService $pdfService)
    {
        $html = $this->renderInvoice($invoice);
        return $pdfService->stream($html, 'invoice-' . $invoice->number);
    }

    protected function renderInvoice(Invoice $invoice): string
    {
        $invoice->load('project', 'bankAccount', 'category');

        return view('pdf.invoice', [
            'invoice' => $invoice,
            'company' => [
                'name' => Setting::get('company_name', 'Event Plus'),
                'email' => Setting::get('company_email'),
                'phone' => Setting::get('company_phone'),
                'address' => Setting::get('company_address'),
            ],
            'settings' => [
                'invoice_notes' => Setting::get('invoice_notes'),
            ],
        ])->render();
    }

    protected function formData(): array
    {
        return [
            'projects' => Project::orderBy('name')->get(),
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ];
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
}
