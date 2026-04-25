<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalEntry::with('lines.account', 'project')->latest('entry_date');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%$search%")
                  ->orWhere('reference', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        $entries = $query->paginate(20)->withQueryString();

        return view('journal-entries.index', compact('entries'));
    }

    public function create()
    {
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        $projects = Project::orderBy('name')->get();
        return view('journal-entries.create', compact('accounts', 'projects'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::transaction(function () use ($data, $request) {
            $entry = JournalEntry::create([
                'number' => JournalEntry::generateNumber(),
                'entry_date' => $data['entry_date'],
                'reference' => $data['reference'] ?? null,
                'description' => $data['description'] ?? null,
                'project_id' => $data['project_id'] ?? null,
                'status' => $request->input('action') === 'post' ? 'posted' : 'draft',
            ]);

            foreach ($data['lines'] as $line) {
                $entry->lines()->create($line);
            }

            $entry->recalculateTotals();
        });

        return redirect()->route('journal-entries.index')->with('success', 'تم حفظ القيد');
    }

    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load('lines.account', 'project', 'source');
        return view('journal-entries.show', ['entry' => $journalEntry]);
    }

    public function edit(JournalEntry $journalEntry)
    {
        if ($journalEntry->status === 'posted') {
            return back()->with('error', 'لا يمكن تعديل قيد منشور');
        }
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        $projects = Project::orderBy('name')->get();
        $journalEntry->load('lines');
        return view('journal-entries.edit', ['entry' => $journalEntry, 'accounts' => $accounts, 'projects' => $projects]);
    }

    public function update(Request $request, JournalEntry $journalEntry)
    {
        if ($journalEntry->status === 'posted') {
            return back()->with('error', 'لا يمكن تعديل قيد منشور');
        }

        $data = $this->validateData($request);

        DB::transaction(function () use ($data, $journalEntry, $request) {
            $journalEntry->update([
                'entry_date' => $data['entry_date'],
                'reference' => $data['reference'] ?? null,
                'description' => $data['description'] ?? null,
                'project_id' => $data['project_id'] ?? null,
                'status' => $request->input('action') === 'post' ? 'posted' : 'draft',
            ]);

            $journalEntry->lines()->delete();
            foreach ($data['lines'] as $line) {
                $journalEntry->lines()->create($line);
            }

            $journalEntry->recalculateTotals();
        });

        return redirect()->route('journal-entries.index')->with('success', 'تم تحديث القيد');
    }

    public function post(JournalEntry $journalEntry)
    {
        $journalEntry->recalculateTotals();
        if (! $journalEntry->isBalanced() || $journalEntry->lines()->count() < 2) {
            return back()->with('error', 'القيد غير متوازن أو لا يحتوي على سطور كافية');
        }
        $journalEntry->update(['status' => 'posted']);
        return back()->with('success', 'تم نشر القيد');
    }

    public function destroy(JournalEntry $journalEntry)
    {
        if ($journalEntry->status === 'posted') {
            return back()->with('error', 'لا يمكن حذف قيد منشور');
        }
        $journalEntry->delete();
        return redirect()->route('journal-entries.index')->with('success', 'تم حذف القيد');
    }

    protected function validateData(Request $request): array
    {
        $data = $request->validate([
            'entry_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'lines.*.debit' => ['required', 'numeric', 'min:0'],
            'lines.*.credit' => ['required', 'numeric', 'min:0'],
            'lines.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $totalDebit = collect($data['lines'])->sum('debit');
        $totalCredit = collect($data['lines'])->sum('credit');
        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'lines' => ['القيد غير متوازن — مجموع المدين يجب أن يساوي مجموع الدائن'],
            ]);
        }

        return $data;
    }
}
