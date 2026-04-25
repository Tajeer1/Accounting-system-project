<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@eventplus.com'],
            [
                'name' => 'مدير النظام',
                'password' => bcrypt('password'),
            ]
        );

        $this->call([
            SettingsSeeder::class,
            CategoriesSeeder::class,
            ChartOfAccountsSeeder::class,
            BankAccountsSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
