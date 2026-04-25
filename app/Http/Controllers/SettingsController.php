<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::allGrouped();
        $categories = Category::orderBy('name')->get();
        return view('settings.index', compact('settings', 'categories'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'currency' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'invoice_due_days' => ['nullable', 'numeric', 'min:0'],
            'invoice_notes' => ['nullable', 'string'],
            'invoice_prefix_sales' => ['nullable', 'string', 'max:20'],
            'invoice_prefix_purchase' => ['nullable', 'string', 'max:20'],
        ]);

        $groups = [
            'company_name' => 'general', 'company_email' => 'general',
            'company_phone' => 'general', 'company_address' => 'general',
            'currency' => 'general', 'currency_symbol' => 'general',
            'invoice_due_days' => 'invoices', 'invoice_notes' => 'invoices',
            'invoice_prefix_sales' => 'invoices', 'invoice_prefix_purchase' => 'invoices',
        ];

        foreach ($data as $key => $value) {
            Setting::set($key, $value, $groups[$key] ?? 'general');
        }

        return back()->with('success', 'تم حفظ الإعدادات');
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:purchase,invoice,general'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        Category::create(array_merge($data, ['is_active' => true]));

        return back()->with('success', 'تم إضافة التصنيف');
    }

    public function destroyCategory(Category $category)
    {
        if ($category->purchases()->exists() || $category->invoices()->exists()) {
            return back()->with('error', 'لا يمكن حذف تصنيف مستخدم');
        }
        $category->delete();
        return back()->with('success', 'تم حذف التصنيف');
    }
}
