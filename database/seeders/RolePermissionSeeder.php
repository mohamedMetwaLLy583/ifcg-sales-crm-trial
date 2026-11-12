<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // إنشاء الصلاحيات
        $permissions = [
            // إدارة المستخدمين
            'view_user',
            'create_user',
            'edit_user',
            'delete_user',

            // إدارة الادوار
            'view_role',
            'create_role',
            'edit_role',
            'delete_role',

            // إدارة التصنيفات
            'view_category',
            'create_category',
            'edit_category',
            'delete_category',

            // إدارة المنتجات
            'view_product',
            'create_product',
            'edit_product',
            'delete_product',

            // إدارة الطلبات
            'manage_order',
            'view_order',
            'edit_order',
            'delete_order',

            // إدارة العملاء
            'manage_customer',
            'view_customer',
            'edit_customer',
            'delete_customer',

            // إدارة الإعدادات
            'manage_settings',

            // التقارير
            'view_reports',

            //  contact message
            'send_message',
            'view_messages',
            'delete_message',


            // صلاحيات خاصة بالعميل
            // 'customer_view_product',
            // 'customer_create_order',
            // 'customer_edit_order',
            // 'customer_view_order',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // إنشاء الأدوار وتعيين الصلاحيات
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        $customerRole = Role::create(['name' => 'customer', 'guard_name' => 'web']);
        // $customerRole->givePermissionTo([
        //     'customer_view_product',
        //     'customer_create_order',
        //     'customer_edit_order',
        //     'customer_view_order',
        //     'send_message',

        // ]);
    }
}
