<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'مستلزمات مكتبية', 'type' => 'purchase', 'color' => '#6366f1'],
            ['name' => 'تجهيزات معارض', 'type' => 'purchase', 'color' => '#f59e0b'],
            ['name' => 'إيجارات', 'type' => 'purchase', 'color' => '#ef4444'],
            ['name' => 'تسويق', 'type' => 'purchase', 'color' => '#10b981'],
            ['name' => 'مبيعات خدمات', 'type' => 'invoice', 'color' => '#3b82f6'],
            ['name' => 'مبيعات منتجات', 'type' => 'invoice', 'color' => '#8b5cf6'],
            ['name' => 'عام', 'type' => 'general', 'color' => '#64748b'],
        ];

        foreach ($items as $item) {
            Category::updateOrCreate(['name' => $item['name']], $item);
        }
    }
}
