<?php

namespace Database\Seeders;

use App\Models\Finance\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Food', 'Transport', 'Rent', 'Education', 'Health', 'Shopping', 'Entertainment', 'Other'] as $name) {
            ExpenseCategory::query()->firstOrCreate([
                'user_id' => null,
                'name' => $name,
            ]);
        }
    }
}
