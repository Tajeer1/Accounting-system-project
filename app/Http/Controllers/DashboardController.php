<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Project;
use App\Models\Purchase;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBalance = BankAccount::where('is_active', true)->sum('current_balance');
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();

        $salesInvoicesTotal = Invoice::where('type', 'sales')->sum('amount');
        $purchaseInvoicesTotal = Invoice::where('type', 'purchase')->sum('amount');

        $thisMonthPurchases = Purchase::where('status', 'paid')
            ->whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year)
            ->sum('amount');

        $lastMonthPurchases = Purchase::where('status', 'paid')
            ->whereMonth('purchase_date', now()->subMonth()->month)
            ->whereYear('purchase_date', now()->subMonth()->year)
            ->sum('amount');

        $purchasesTrend = $lastMonthPurchases > 0
            ? round((($thisMonthPurchases - $lastMonthPurchases) / $lastMonthPurchases) * 100, 1)
            : 0;

        $activeProjects = Project::where('status', 'in_progress')->count();

        $latestPurchases = Purchase::with('category', 'project')->latest('purchase_date')->take(5)->get();
        $latestInvoices = Invoice::with('project')->latest('issue_date')->take(5)->get();
        $latestEntries = JournalEntry::latest('entry_date')->take(5)->get();
        $projectsActive = Project::where('status', 'in_progress')->take(6)->get();

        $monthlyData = $this->monthlyChart();

        return view('dashboard', compact(
            'totalBalance', 'bankAccounts', 'salesInvoicesTotal', 'purchaseInvoicesTotal',
            'thisMonthPurchases', 'purchasesTrend', 'activeProjects',
            'latestPurchases', 'latestInvoices', 'latestEntries', 'projectsActive',
            'monthlyData'
        ));
    }

    protected function monthlyChart(): array
    {
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push([
                'label' => $date->translatedFormat('M'),
                'month' => $date->month,
                'year' => $date->year,
            ]);
        }

        return $months->map(function ($m) {
            return [
                'label' => $m['label'],
                'revenue' => (float) Invoice::where('type', 'sales')
                    ->whereMonth('issue_date', $m['month'])
                    ->whereYear('issue_date', $m['year'])
                    ->sum('amount'),
                'expense' => (float) Purchase::whereMonth('purchase_date', $m['month'])
                    ->whereYear('purchase_date', $m['year'])
                    ->sum('amount'),
            ];
        })->toArray();
    }
}
