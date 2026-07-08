<?php

namespace Database\Seeders;

use App\Models\IncomeSource;
use Illuminate\Database\Seeder;

class IncomeSourceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Salary', 'Freelance', 'Business', 'Investment', 'Gift', 'Other'] as $name) {
            IncomeSource::query()->firstOrCreate([
                'user_id' => null,
                'name' => $name,
            ], [
                'description' => null,
                'is_default' => true,
            ]);
        }
    }
}
