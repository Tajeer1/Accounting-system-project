<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Project;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends Controller
{
    public function index(): JsonResponse
    {
        $thisMonth = Purchase::where('status', 'paid')
            ->whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year)
            ->sum('amount');

        $lastMonth = Purchase::where('status', 'paid')
            ->whereMonth('purchase_date', now()->subMonth()->month)
            ->whereYear('purchase_date', now()->subMonth()->year)
            ->sum('amount');

        $trend = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;

        return response()->json([
            'stats' => [
                'total_balance' => (float) BankAccount::where('is_active', true)->sum('current_balance'),
                'sales_invoices_total' => (float) Invoice::where('type', 'sales')->sum('amount'),
                'purchase_invoices_total' => (float) Invoice::where('type', 'purchase')->sum('amount'),
                'this_month_purchases' => (float) $thisMonth,
                'purchases_trend' => (float) $trend,
                'active_projects' => Project::where('status', 'in_progress')->count(),
                'unpaid_invoices' => (float) Invoice::whereIn('status', ['draft', 'sent', 'overdue'])->sum('amount'),
            ],
            'bank_accounts' => BankAccount::where('is_active', true)->orderBy('name')->get()->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'type' => $b->type,
                'type_label' => $b->typeLabel(),
                'current_balance' => (float) $b->current_balance,
                'currency' => $b->currency,
            ]),
            'monthly_chart' => $this->monthlyChart(),
            'latest_purchases' => Purchase::with('category', 'project')->latest('purchase_date')->take(5)->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'number' => $p->number,
                    'supplier_name' => $p->supplier_name,
                    'amount' => (float) $p->amount,
                    'purchase_date' => $p->purchase_date->toDateString(),
                    'category' => $p->category?->name,
                    'project' => $p->project?->name,
                    'status' => $p->status,
                ]),
            'latest_invoices' => Invoice::with('project')->latest('issue_date')->take(5)->get()
                ->map(fn ($i) => [
                    'id' => $i->id,
                    'number' => $i->number,
                    'type' => $i->type,
                    'type_label' => $i->typeLabel(),
                    'party_name' => $i->party_name,
                    'amount' => (float) $i->amount,
                    'issue_date' => $i->issue_date->toDateString(),
                    'status' => $i->status,
                    'status_label' => $i->statusLabel(),
                ]),
            'latest_entries' => JournalEntry::latest('entry_date')->take(5)->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'number' => $e->number,
                    'description' => $e->description,
                    'total_debit' => (float) $e->total_debit,
                    'entry_date' => $e->entry_date->toDateString(),
                    'status' => $e->status,
                ]),
        ]);
    }

    protected function monthlyChart(): array
    {
        $result = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $result[] = [
                'label' => $date->translatedFormat('M'),
                'revenue' => (float) Invoice::where('type', 'sales')
                    ->whereMonth('issue_date', $date->month)
                    ->whereYear('issue_date', $date->year)
                    ->sum('amount'),
                'expense' => (float) Purchase::whereMonth('purchase_date', $date->month)
                    ->whereYear('purchase_date', $date->year)
                    ->sum('amount'),
            ];
        }
        return $result;
    }
}
