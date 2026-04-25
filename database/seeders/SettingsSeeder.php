<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'general' => [
                'company_name' => 'Event Plus',
                'company_email' => 'info@eventplus.com',
                'company_phone' => '+96890000000',
                'company_address' => 'مسقط، سلطنة عُمان',
                'currency' => 'OMR',
                'currency_symbol' => 'ر.ع',
                'currency_decimals' => '3',
            ],
            'invoices' => [
                'invoice_prefix_sales' => 'INV-S-',
                'invoice_prefix_purchase' => 'INV-P-',
                'invoice_due_days' => '30',
                'invoice_notes' => 'شكرًا لتعاملكم معنا',
            ],
            'numbering' => [
                'purchase_prefix' => 'PO-',
                'journal_prefix' => 'JE-',
                'transfer_prefix' => 'TRF-',
            ],
        ];

        foreach ($defaults as $group => $items) {
            foreach ($items as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => $group]
                );
            }
        }
    }
}
