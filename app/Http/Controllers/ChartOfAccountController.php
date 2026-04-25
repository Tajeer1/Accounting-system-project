<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        $roots = ChartOfAccount::with('children.children')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        return view('chart-of-accounts.index', compact('roots'));
    }

    public function create()
    {
        $parents = ChartOfAccount::orderBy('code')->get();
        return view('chart-of-accounts.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:chart_of_accounts,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:asset,liability,equity,revenue,expense'],
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['level'] = $data['parent_id']
            ? (ChartOfAccount::find($data['parent_id'])->level + 1)
            : 1;
        $data['is_active'] = $request->boolean('is_active', true);

        ChartOfAccount::create($data);

        return redirect()->route('chart-of-accounts.index')->with('success', 'تم إضافة الحساب بنجاح');
    }

    public function edit(ChartOfAccount $chartOfAccount)
    {
        $parents = ChartOfAccount::where('id', '!=', $chartOfAccount->id)->orderBy('code')->get();
        return view('chart-of-accounts.edit', ['account' => $chartOfAccount, 'parents' => $parents]);
    }

    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:chart_of_accounts,code,' . $chartOfAccount->id],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:asset,liability,equity,revenue,expense'],
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['level'] = $data['parent_id']
            ? (ChartOfAccount::find($data['parent_id'])->level + 1)
            : 1;
        $data['is_active'] = $request->boolean('is_active', true);

        $chartOfAccount->update($data);

        return redirect()->route('chart-of-accounts.index')->with('success', 'تم تحديث الحساب بنجاح');
    }

    public function destroy(ChartOfAccount $chartOfAccount)
    {
        if ($chartOfAccount->children()->exists() || $chartOfAccount->lines()->exists()) {
            return back()->with('error', 'لا يمكن حذف حساب له حسابات فرعية أو حركات');
        }

        $chartOfAccount->delete();
        return redirect()->route('chart-of-accounts.index')->with('success', 'تم حذف الحساب');
    }
}
