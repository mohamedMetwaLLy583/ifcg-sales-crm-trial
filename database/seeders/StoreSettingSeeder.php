<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSettingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('store_settings')->insertOrIgnore([
            ['key' => 'store_name', 'value' => 'اثاث للديكور', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'store_whatsapp', 'value' => '', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'store_email', 'value' => '', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'store_address', 'value' => '', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'store_currency', 'value' => 'EGP', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'store_logo_path', 'value' => '', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
