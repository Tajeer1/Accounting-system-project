<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'الحساب البنكي الرئيسي',
                'type' => 'bank',
                'account_number' => 'OM0100000000000000001',
                'opening_balance' => 25000,
                'current_balance' => 25000,
                'currency' => 'OMR',
            ],
            [
                'name' => 'حساب بنكي ثانوي',
                'type' => 'bank',
                'account_number' => 'OM0100000000000000002',
                'opening_balance' => 5000,
                'current_balance' => 5000,
                'currency' => 'OMR',
            ],
            [
                'name' => 'الخزينة النقدية',
                'type' => 'cash',
                'opening_balance' => 1000,
                'current_balance' => 1000,
                'currency' => 'OMR',
            ],
        ];

        foreach ($accounts as $account) {
            BankAccount::updateOrCreate(['name' => $account['name']], $account);
        }
    }
}
