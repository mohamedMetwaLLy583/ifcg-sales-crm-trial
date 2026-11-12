<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingZoneSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('shipping_zones')->insertOrIgnore([
            [
                'name' => 'القاهرة والجيزة',
                'cities' => json_encode(['Cairo', 'Giza', 'القاهرة', 'الجيزة'], JSON_UNESCAPED_UNICODE),
                'shipping_cost' => 0.00,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
