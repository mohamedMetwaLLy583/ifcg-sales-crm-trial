<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolePermissionSeeder::class,
            OrderPermissionsSeeder::class,
            BranchSeeder::class,
            StoreSettingSeeder::class,
            ShippingZoneSeeder::class,
            UserSeeder::class,
            VariationAttributesSeeder::class,
            FurnitureCatalogSeeder::class,

        ]);
    }
}
