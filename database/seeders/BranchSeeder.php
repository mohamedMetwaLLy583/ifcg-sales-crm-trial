<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        if (Branch::query()->exists()) {
            return;
        }

        Branch::create([
            'name' => 'المخزن الرئيسي',
            'is_active' => true,
        ]);
    }
}
