<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            ['code' => '1000', 'name' => 'الأصول', 'type' => 'asset', 'children' => [
                ['code' => '1100', 'name' => 'النقدية والبنوك', 'type' => 'asset'],
                ['code' => '1200', 'name' => 'حسابات العملاء (مدينون)', 'type' => 'asset'],
                ['code' => '1300', 'name' => 'المخزون', 'type' => 'asset'],
                ['code' => '1400', 'name' => 'الأصول الثابتة', 'type' => 'asset'],
            ]],
            ['code' => '2000', 'name' => 'الالتزامات', 'type' => 'liability', 'children' => [
                ['code' => '2100', 'name' => 'حسابات الموردين (دائنون)', 'type' => 'liability'],
                ['code' => '2200', 'name' => 'قروض قصيرة الأجل', 'type' => 'liability'],
                ['code' => '2300', 'name' => 'ضرائب مستحقة', 'type' => 'liability'],
            ]],
            ['code' => '3000', 'name' => 'حقوق الملكية', 'type' => 'equity', 'children' => [
                ['code' => '3100', 'name' => 'رأس المال', 'type' => 'equity'],
                ['code' => '3200', 'name' => 'الأرباح المحتجزة', 'type' => 'equity'],
            ]],
            ['code' => '4000', 'name' => 'الإيرادات', 'type' => 'revenue', 'children' => [
                ['code' => '4100', 'name' => 'إيرادات المبيعات', 'type' => 'revenue'],
                ['code' => '4200', 'name' => 'إيرادات أخرى', 'type' => 'revenue'],
            ]],
            ['code' => '5000', 'name' => 'المصاريف', 'type' => 'expense', 'children' => [
                ['code' => '5100', 'name' => 'المشتريات والمصاريف التشغيلية', 'type' => 'expense'],
                ['code' => '5200', 'name' => 'تكلفة البضاعة المباعة', 'type' => 'expense'],
                ['code' => '5300', 'name' => 'مصاريف الرواتب', 'type' => 'expense'],
                ['code' => '5400', 'name' => 'مصاريف إدارية وعمومية', 'type' => 'expense'],
                ['code' => '5500', 'name' => 'مصاريف تسويق', 'type' => 'expense'],
            ]],
        ];

        foreach ($tree as $parent) {
            $children = $parent['children'] ?? [];
            unset($parent['children']);

            $parentModel = ChartOfAccount::updateOrCreate(
                ['code' => $parent['code']],
                array_merge($parent, ['parent_id' => null, 'level' => 1, 'is_active' => true])
            );

            foreach ($children as $child) {
                ChartOfAccount::updateOrCreate(
                    ['code' => $child['code']],
                    array_merge($child, [
                        'parent_id' => $parentModel->id,
                        'level' => 2,
                        'is_active' => true,
                    ])
                );
            }
        }
    }
}
