<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentPageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('content_pages')->insertOrIgnore([
            [
                'slug' => 'about-us',
                'title' => 'من نحن',
                'meta_title' => 'من نحن | وحيد حسن',
                'meta_description' => 'تعرف على متجر وحيد حسن للبدل الرجالي وخدمة البيع والإيجار.',
                'body' => 'وحيد حسن متجر متخصص في بيع وتأجير البدل الرجالي للمناسبات والزفاف، مع اختيارات رسمية وخدمة تنسيق قبل الطلب.',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'privacy-policy',
                'title' => 'سياسة الخصوصية',
                'meta_title' => 'سياسة الخصوصية | وحيد حسن',
                'meta_description' => 'سياسة الخصوصية الخاصة بمتجر وحيد حسن.',
                'body' => 'نحافظ على بيانات العملاء ونستخدمها فقط لمعالجة الطلبات والحجوزات وتحسين الخدمة والتواصل بخصوص الطلبات.',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'terms',
                'title' => 'الشروط والأحكام',
                'meta_title' => 'الشروط والأحكام | وحيد حسن',
                'meta_description' => 'الشروط والأحكام الخاصة بالشراء أو الحجز من متجر وحيد حسن.',
                'body' => 'إرسال الطلب يعني موافقتك على مراجعة بيانات الطلب والتواصل لتأكيد التفاصيل قبل الشحن أو الحجز عند الحاجة.',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
