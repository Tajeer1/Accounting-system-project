<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Purchase;
use App\Services\AccountingService;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(AccountingService $accounting): void
    {
        $projects = [
            [
                'code' => 'EXH-001',
                'name' => 'معرض مسقط للأعمال 2026',
                'client_name' => 'غرفة تجارة وصناعة عُمان',
                'start_date' => now()->subDays(45),
                'end_date' => now()->addDays(15),
                'contract_value' => 35000,
                'status' => 'in_progress',
            ],
            [
                'code' => 'EXH-002',
                'name' => 'معرض صلالة التقني',
                'client_name' => 'شركة التقنية المتقدمة',
                'start_date' => now()->subDays(90),
                'end_date' => now()->subDays(20),
                'contract_value' => 18000,
                'status' => 'completed',
            ],
            [
                'code' => 'EXH-003',
                'name' => 'معرض الصحة الدولي',
                'client_name' => 'وزارة الصحة',
                'start_date' => now()->addDays(20),
                'end_date' => now()->addDays(60),
                'contract_value' => 42000,
                'status' => 'planned',
            ],
        ];

        foreach ($projects as $p) {
            Project::updateOrCreate(['code' => $p['code']], $p);
        }

        if (Purchase::count() > 0) {
            return;
        }

        $mainBank = BankAccount::where('name', 'الحساب البنكي الرئيسي')->first();
        $cash = BankAccount::where('name', 'الخزينة النقدية')->first();
        $catPurchase = Category::where('type', 'purchase')->first();
        $catInvoice = Category::where('type', 'invoice')->first();
        $project1 = Project::where('code', 'EXH-001')->first();
        $project2 = Project::where('code', 'EXH-002')->first();

        $purchases = [
            [
                'supplier_name' => 'شركة المعدات الحديثة',
                'amount' => 4500,
                'purchase_date' => now()->subDays(30),
                'category_id' => $catPurchase?->id,
                'bank_account_id' => $mainBank?->id,
                'project_id' => $project1?->id,
                'description' => 'تجهيزات جناح المعرض',
            ],
            [
                'supplier_name' => 'مطبعة الألوان',
                'amount' => 850,
                'purchase_date' => now()->subDays(20),
                'category_id' => $catPurchase?->id,
                'bank_account_id' => $cash?->id,
                'project_id' => $project1?->id,
                'description' => 'بروشورات وطباعة',
            ],
            [
                'supplier_name' => 'شركة اللوجستيات السريعة',
                'amount' => 1200,
                'purchase_date' => now()->subDays(10),
                'category_id' => $catPurchase?->id,
                'bank_account_id' => $mainBank?->id,
                'project_id' => $project2?->id,
                'description' => 'شحن ونقل',
            ],
            [
                'supplier_name' => 'مكتب العقارات',
                'amount' => 2200,
                'purchase_date' => now()->subDays(5),
                'category_id' => $catPurchase?->id,
                'bank_account_id' => $mainBank?->id,
                'description' => 'إيجار المكتب',
            ],
        ];

        foreach ($purchases as $data) {
            $purchase = Purchase::create(array_merge($data, [
                'number' => Purchase::generateNumber(),
                'status' => 'paid',
            ]));
            $accounting->createEntryForPurchase($purchase);
        }

        $invoices = [
            [
                'type' => 'sales',
                'party_name' => 'غرفة تجارة وصناعة عُمان',
                'amount' => 17500,
                'issue_date' => now()->subDays(40),
                'due_date' => now()->subDays(10),
                'status' => 'paid',
                'project_id' => $project1?->id,
                'bank_account_id' => $mainBank?->id,
                'category_id' => $catInvoice?->id,
                'description' => 'دفعة أولى من عقد المعرض',
            ],
            [
                'type' => 'sales',
                'party_name' => 'شركة التقنية المتقدمة',
                'amount' => 18000,
                'issue_date' => now()->subDays(25),
                'due_date' => now()->addDays(5),
                'status' => 'sent',
                'project_id' => $project2?->id,
                'bank_account_id' => $mainBank?->id,
                'category_id' => $catInvoice?->id,
                'description' => 'فاتورة تسليم المعرض',
            ],
            [
                'type' => 'purchase',
                'party_name' => 'شركة المعدات الحديثة',
                'amount' => 4500,
                'issue_date' => now()->subDays(30),
                'due_date' => now()->subDays(15),
                'status' => 'paid',
                'project_id' => $project1?->id,
                'bank_account_id' => $mainBank?->id,
                'description' => 'فاتورة تجهيزات',
            ],
        ];

        foreach ($invoices as $data) {
            $invoice = Invoice::create(array_merge($data, [
                'number' => Invoice::generateNumber($data['type']),
            ]));
            $accounting->createEntryForInvoice($invoice);
        }

        BankAccount::all()->each->recalculateBalance();
    }
}
