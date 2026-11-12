<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VariationAttribute;
use App\Models\AttributeOption;
use Illuminate\Support\Facades\Schema;

class VariationAttributesSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        
        AttributeOption::truncate();
        VariationAttribute::truncate();
        
        Schema::enableForeignKeyConstraints();

        $attributes = [
            // السمات الأساسية للملابس
            [
                'name' => 'color',
                'display_name' => 'اللون',
                'input_type' => 'color',
                'options' => [
                    ['value' => 'red', 'display_value' => 'أحمر', 'color_code' => '#FF0000'],
                    ['value' => 'blue', 'display_value' => 'أزرق', 'color_code' => '#0000FF'],
                    ['value' => 'green', 'display_value' => 'أخضر', 'color_code' => '#008000'],
                    ['value' => 'black', 'display_value' => 'أسود', 'color_code' => '#000000'],
                    ['value' => 'white', 'display_value' => 'أبيض', 'color_code' => '#FFFFFF'],
                    ['value' => 'yellow', 'display_value' => 'أصفر', 'color_code' => '#FFFF00'],
                    ['value' => 'purple', 'display_value' => 'بنفسجي', 'color_code' => '#800080'],
                    ['value' => 'pink', 'display_value' => 'وردي', 'color_code' => '#FFC0CB'],
                    ['value' => 'orange', 'display_value' => 'برتقالي', 'color_code' => '#FFA500'],
                    ['value' => 'gray', 'display_value' => 'رمادي', 'color_code' => '#808080'],
                    ['value' => 'brown', 'display_value' => 'بني', 'color_code' => '#A52A2A'],
                    ['value' => 'navy', 'display_value' => 'كحلي', 'color_code' => '#000080'],
                ]
            ],
            [
                'name' => 'size',
                'display_name' => 'المقاس',
                'input_type' => 'select',
                'options' => [
                    ['value' => 'xs', 'display_value' => 'XS - صغير جداً'],
                    ['value' => 's', 'display_value' => 'S - صغير'],
                    ['value' => 'm', 'display_value' => 'M - متوسط'],
                    ['value' => 'l', 'display_value' => 'L - كبير'],
                    ['value' => 'xl', 'display_value' => 'XL - كبير جداً'],
                    ['value' => 'xxl', 'display_value' => 'XXL - ضخم'],
                    ['value' => 'xxxl', 'display_value' => 'XXXL - ضخم جداً'],
                    ['value' => '32', 'display_value' => '32'],
                    ['value' => '34', 'display_value' => '34'],
                    ['value' => '36', 'display_value' => '36'],
                    ['value' => '38', 'display_value' => '38'],
                    ['value' => '40', 'display_value' => '40'],
                    ['value' => '42', 'display_value' => '42'],
                    ['value' => '44', 'display_value' => '44'],
                    ['value' => '46', 'display_value' => '46'],
                ]
            ],
            [
                'name' => 'material',
                'display_name' => 'المادة',
                'input_type' => 'select',
                'options' => [
                    ['value' => 'cotton', 'display_value' => 'قطن'],
                    ['value' => 'polyester', 'display_value' => 'بوليستر'],
                    ['value' => 'wool', 'display_value' => 'صوف'],
                    ['value' => 'silk', 'display_value' => 'حرير'],
                    ['value' => 'linen', 'display_value' => 'كتان'],
                    ['value' => 'denim', 'display_value' => 'دينيم'],
                    ['value' => 'leather', 'display_value' => 'جلد'],
                    ['value' => 'suede', 'display_value' => 'جلد مغبر'],
                    ['value' => 'nylon', 'display_value' => 'نايلون'],
                    ['value' => 'spandex', 'display_value' => 'سباندكس'],
                ]
            ],
            // سمات الإلكترونيات
            [
                'name' => 'storage',
                'display_name' => 'سعة التخزين',
                'input_type' => 'select',
                'options' => [
                    ['value' => '64gb', 'display_value' => '64 جيجابايت'],
                    ['value' => '128gb', 'display_value' => '128 جيجابايت'],
                    ['value' => '256gb', 'display_value' => '256 جيجابايت'],
                    ['value' => '512gb', 'display_value' => '512 جيجابايت'],
                    ['value' => '1tb', 'display_value' => '1 تيرابايت'],
                    ['value' => '2tb', 'display_value' => '2 تيرابايت'],
                ]
            ],
            [
                'name' => 'ram',
                'display_name' => 'الذاكرة العشوائية',
                'input_type' => 'select',
                'options' => [
                    ['value' => '4gb', 'display_value' => '4 جيجابايت'],
                    ['value' => '8gb', 'display_value' => '8 جيجابايت'],
                    ['value' => '16gb', 'display_value' => '16 جيجابايت'],
                    ['value' => '32gb', 'display_value' => '32 جيجابايت'],
                    ['value' => '64gb', 'display_value' => '64 جيجابايت'],
                ]
            ],
            [
                'name' => 'screen_size',
                'display_name' => 'حجم الشاشة',
                'input_type' => 'select',
                'options' => [
                    ['value' => '13', 'display_value' => '13 بوصة'],
                    ['value' => '14', 'display_value' => '14 بوصة'],
                    ['value' => '15.6', 'display_value' => '15.6 بوصة'],
                    ['value' => '17', 'display_value' => '17 بوصة'],
                    ['value' => '24', 'display_value' => '24 بوصة'],
                    ['value' => '27', 'display_value' => '27 بوصة'],
                    ['value' => '32', 'display_value' => '32 بوصة'],
                ]
            ],
            // سمات الأحذية
            [
                'name' => 'shoe_size',
                'display_name' => 'مقاس الحذاء',
                'input_type' => 'select',
                'options' => [
                    ['value' => '36', 'display_value' => '36'],
                    ['value' => '37', 'display_value' => '37'],
                    ['value' => '38', 'display_value' => '38'],
                    ['value' => '39', 'display_value' => '39'],
                    ['value' => '40', 'display_value' => '40'],
                    ['value' => '41', 'display_value' => '41'],
                    ['value' => '42', 'display_value' => '42'],
                    ['value' => '43', 'display_value' => '43'],
                    ['value' => '44', 'display_value' => '44'],
                    ['value' => '45', 'display_value' => '45'],
                ]
            ],
            // سمات عامة
            [
                'name' => 'warranty',
                'display_name' => 'الضمان',
                'input_type' => 'select',
                'options' => [
                    ['value' => 'no_warranty', 'display_value' => 'بدون ضمان'],
                    ['value' => '3_months', 'display_value' => '3 شهور'],
                    ['value' => '6_months', 'display_value' => '6 شهور'],
                    ['value' => '1_year', 'display_value' => '1 سنة'],
                    ['value' => '2_years', 'display_value' => '2 سنة'],
                    ['value' => '3_years', 'display_value' => '3 سنة'],
                    ['value' => '5_years', 'display_value' => '5 سنة'],
                ]
            ],
            [
                'name' => 'brand',
                'display_name' => 'العلامة التجارية',
                'input_type' => 'select',
                'options' => [
                    ['value' => 'nike', 'display_value' => 'نايكي'],
                    ['value' => 'adidas', 'display_value' => 'أديداس'],
                    ['value' => 'apple', 'display_value' => 'أبل'],
                    ['value' => 'samsung', 'display_value' => 'سامسونج'],
                    ['value' => 'xiaomi', 'display_value' => 'شاومي'],
                    ['value' => 'huawei', 'display_value' => 'هواوي'],
                    ['value' => 'dell', 'display_value' => 'ديل'],
                    ['value' => 'hp', 'display_value' => 'اتش بي'],
                    ['value' => 'lenovo', 'display_value' => 'لينوفو'],
                ]
            ],
        ];

        foreach ($attributes as $attributeData) {
            $attribute = VariationAttribute::create([
                'name' => $attributeData['name'],
                'display_name' => $attributeData['display_name'],
                'input_type' => $attributeData['input_type'],
                'is_active' => true
            ]);

            foreach ($attributeData['options'] as $index => $optionData) {
                AttributeOption::create([
                    'attribute_id' => $attribute->id,
                    'value' => $optionData['value'],
                    'display_value' => $optionData['display_value'],
                    'color_code' => $optionData['color_code'] ?? null,
                    'sort_order' => $index,
                    'is_active' => true
                ]);
            }
        }

        $this->command->info('✅ Variation attributes and options seeded successfully!');
        $this->command->info('📊 Total attributes: ' . VariationAttribute::count());
        $this->command->info('📋 Total options: ' . AttributeOption::count());
    }
}
