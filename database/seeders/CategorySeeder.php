<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            // الإلكترونيات والأجهزة
            [
                'name' => 'الإلكترونيات',
                'slug' => 'electronics',
                'description' => 'جميع الأجهزة الإلكترونية والكهربائية',
                'is_active' => true,
                'children' => [
                    [
                        'name' => 'الهواتف الذكية',
                        'slug' => 'smartphones',
                        'description' => 'الهواتف الذكية وملحقاتها',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'آيفون', 'slug' => 'iphone', 'is_active' => true],
                            ['name' => 'سامسونج', 'slug' => 'samsung', 'is_active' => true],
                            ['name' => 'شاومي', 'slug' => 'xiaomi', 'is_active' => true],
                            ['name' => 'هواوي', 'slug' => 'huawei', 'is_active' => true],
                            ['name' => 'أوبو', 'slug' => 'oppo', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'أجهزة الكمبيوتر',
                        'slug' => 'computers',
                        'description' => 'أجهزة الكمبيوتر والمحمول',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'لابتوب', 'slug' => 'laptops', 'is_active' => true],
                            ['name' => 'كمبيوتر مكتبي', 'slug' => 'desktop-pcs', 'is_active' => true],
                            ['name' => 'أجهزة الآيباد', 'slug' => 'ipads', 'is_active' => true],
                            ['name' => 'التابلت', 'slug' => 'tablets', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'التلفزيونات',
                        'slug' => 'televisions',
                        'description' => 'شاشات التلفزيون بأنواعها',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'شاشات سمارت', 'slug' => 'smart-tv', 'is_active' => true],
                            ['name' => 'شاشات LED', 'slug' => 'led-tv', 'is_active' => true],
                            ['name' => 'شاشات OLED', 'slug' => 'oled-tv', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'الكاميرات',
                        'slug' => 'cameras',
                        'description' => 'الكاميرات الرقمية وملحقاتها',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'كاميرات DSLR', 'slug' => 'dslr-cameras', 'is_active' => true],
                            ['name' => 'كاميرات mirrorless', 'slug' => 'mirrorless-cameras', 'is_active' => true],
                            ['name' => 'كاميرات فيديو', 'slug' => 'video-cameras', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'الأجهزة المنزلية',
                        'slug' => 'home-appliances',
                        'description' => 'الأجهزة الكهربائية المنزلية',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'الغسالات', 'slug' => 'washing-machines', 'is_active' => true],
                            ['name' => 'الثلاجات', 'slug' => 'refrigerators', 'is_active' => true],
                            ['name' => 'المكيفات', 'slug' => 'air-conditioners', 'is_active' => true],
                            ['name' => 'السخانات', 'slug' => 'water-heaters', 'is_active' => true],
                        ]
                    ]
                ]
            ],

            // الأزياء والملابس
            [
                'name' => 'الأزياء',
                'slug' => 'fashion',
                'description' => 'ملابس وأزياء للرجال والنساء',
                'is_active' => true,
                'children' => [
                    [
                        'name' => 'ملابس رجالية',
                        'slug' => 'mens-clothing',
                        'description' => 'ملابس وأزياء للرجال',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'تيشيرتات', 'slug' => 'mens-t-shirts', 'is_active' => true],
                            ['name' => 'قمصان', 'slug' => 'mens-shirts', 'is_active' => true],
                            ['name' => 'بناطيل', 'slug' => 'mens-pants', 'is_active' => true],
                            ['name' => 'بدلات', 'slug' => 'mens-suits', 'is_active' => true],
                            ['name' => 'ملابس داخلية', 'slug' => 'mens-underwear', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'ملابس نسائية',
                        'slug' => 'womens-clothing',
                        'description' => 'ملابس وأزياء للنساء',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'فساتين', 'slug' => 'womens-dresses', 'is_active' => true],
                            ['name' => 'بلوزات', 'slug' => 'womens-blouses', 'is_active' => true],
                            ['name' => 'تنانير', 'slug' => 'womens-skirts', 'is_active' => true],
                            ['name' => 'بناطيل نسائية', 'slug' => 'womens-pants', 'is_active' => true],
                            ['name' => 'ملابس داخلية نسائية', 'slug' => 'womens-lingerie', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'أحذية',
                        'slug' => 'shoes',
                        'description' => 'أحذية للرجال والنساء',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'أحذية رجالية', 'slug' => 'mens-shoes', 'is_active' => true],
                            ['name' => 'أحذية نسائية', 'slug' => 'womens-shoes', 'is_active' => true],
                            ['name' => 'أحذية رياضية', 'slug' => 'sneakers', 'is_active' => true],
                            ['name' => 'صنادل', 'slug' => 'sandals', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'الإكسسوارات',
                        'slug' => 'accessories',
                        'description' => 'إكسسوارات الموضة',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'ساعات', 'slug' => 'watches', 'is_active' => true],
                            ['name' => 'نظارات', 'slug' => 'eyewear', 'is_active' => true],
                            ['name' => 'حقائب', 'slug' => 'bags', 'is_active' => true],
                            ['name' => 'مجوهرات', 'slug' => 'jewelry', 'is_active' => true],
                        ]
                    ]
                ]
            ],

            // الصحة والجمال
            [
                'name' => 'الصحة والجمال',
                'slug' => 'health-beauty',
                'description' => 'منتجات العناية بالصحة والجمال',
                'is_active' => true,
                'children' => [
                    [
                        'name' => 'العناية بالبشرة',
                        'slug' => 'skin-care',
                        'description' => 'منتجات العناية بالبشرة',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'مرطبات', 'slug' => 'moisturizers', 'is_active' => true],
                            ['name' => 'منظفات البشرة', 'slug' => 'face-cleansers', 'is_active' => true],
                            ['name' => 'مقشرات', 'slug' => 'exfoliators', 'is_active' => true],
                            ['name' => 'واقي الشمس', 'slug' => 'sunscreen', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'العناية بالشعر',
                        'slug' => 'hair-care',
                        'description' => 'منتجات العناية بالشعر',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'شامبو', 'slug' => 'shampoo', 'is_active' => true],
                            ['name' => 'بلسم', 'slug' => 'conditioner', 'is_active' => true],
                            ['name' => 'زيوت الشعر', 'slug' => 'hair-oils', 'is_active' => true],
                            ['name' => 'مستحضرات تصفيف الشعر', 'slug' => 'hair-styling', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'مستحضرات التجميل',
                        'slug' => 'cosmetics',
                        'description' => 'مستحضرات الماكياج والتجميل',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'أحمر الشفاه', 'slug' => 'lipstick', 'is_active' => true],
                            ['name' => 'ماسكارا', 'slug' => 'mascara', 'is_active' => true],
                            ['name' => 'أساس', 'slug' => 'foundation', 'is_active' => true],
                            ['name' => 'ظلال العيون', 'slug' => 'eye-shadow', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'العطور',
                        'slug' => 'fragrances',
                        'description' => 'عطور للرجال والنساء',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'عطور رجالية', 'slug' => 'mens-fragrances', 'is_active' => true],
                            ['name' => 'عطور نسائية', 'slug' => 'womens-fragrances', 'is_active' => true],
                        ]
                    ]
                ]
            ],

            // المنزل والحديقة
            [
                'name' => 'المنزل والحديقة',
                'slug' => 'home-garden',
                'description' => 'منتجات للبيت والحديقة',
                'is_active' => true,
                'children' => [
                    [
                        'name' => 'أثاث',
                        'slug' => 'furniture',
                        'description' => 'أثاث المنزل',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'أثاث غرف النوم', 'slug' => 'bedroom-furniture', 'is_active' => true],
                            ['name' => 'أثاث غرف المعيشة', 'slug' => 'living-room-furniture', 'is_active' => true],
                            ['name' => 'أثاث المطبخ', 'slug' => 'kitchen-furniture', 'is_active' => true],
                            ['name' => 'أثاث المكاتب', 'slug' => 'office-furniture', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'ديكور',
                        'slug' => 'home-decor',
                        'description' => 'ديكورات المنزل',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'سجاد', 'slug' => 'carpets', 'is_active' => true],
                            ['name' => 'ستائر', 'slug' => 'curtains', 'is_active' => true],
                            ['name' => 'لوحات فنية', 'slug' => 'art-paintings', 'is_active' => true],
                            ['name' => 'تحف وزينة', 'slug' => 'decorative-items', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'أدوات المطبخ',
                        'slug' => 'kitchen-utensils',
                        'description' => 'أدوات وأواني المطبخ',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'أواني طهي', 'slug' => 'cookware', 'is_active' => true],
                            ['name' => 'أدوات المائدة', 'slug' => 'tableware', 'is_active' => true],
                            ['name' => 'أجهزة المطبخ', 'slug' => 'kitchen-appliances', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'الحديقة',
                        'slug' => 'garden',
                        'description' => 'منتجات العناية بالحديقة',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'أدوات الحدائق', 'slug' => 'gardening-tools', 'is_active' => true],
                            ['name' => 'نباتات', 'slug' => 'plants', 'is_active' => true],
                            ['name' => 'أثاث الحدائق', 'slug' => 'garden-furniture', 'is_active' => true],
                        ]
                    ]
                ]
            ],

            // الرياضة واللياقة
            [
                'name' => 'الرياضة واللياقة',
                'slug' => 'sports-fitness',
                'description' => 'منتجات رياضية ولياقة بدنية',
                'is_active' => true,
                'children' => [
                    [
                        'name' => 'ملابس رياضية',
                        'slug' => 'sportswear',
                        'description' => 'ملابس للرياضة والتمرين',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'ملابس رياضية رجالية', 'slug' => 'mens-sportswear', 'is_active' => true],
                            ['name' => 'ملابس رياضية نسائية', 'slug' => 'womens-sportswear', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'أحذية رياضية',
                        'slug' => 'sports-shoes',
                        'description' => 'أحذية مخصصة للرياضة',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'أحذية ركض', 'slug' => 'running-shoes', 'is_active' => true],
                            ['name' => 'أحذية كرة قدم', 'slug' => 'football-shoes', 'is_active' => true],
                            ['name' => 'أحذية كرة سلة', 'slug' => 'basketball-shoes', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'معدات رياضية',
                        'slug' => 'sports-equipment',
                        'description' => 'معدات وأجهزة رياضية',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'أوزان', 'slug' => 'weights', 'is_active' => true],
                            ['name' => 'أجهزة cardio', 'slug' => 'cardio-machines', 'is_active' => true],
                            ['name' => 'معدات اليوجا', 'slug' => 'yoga-equipment', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'رياضات متنوعة',
                        'slug' => 'various-sports',
                        'description' => 'معدات لرياضات مختلفة',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'كرة القدم', 'slug' => 'football', 'is_active' => true],
                            ['name' => 'كرة السلة', 'slug' => 'basketball', 'is_active' => true],
                            ['name' => 'التنس', 'slug' => 'tennis', 'is_active' => true],
                            ['name' => 'السباحة', 'slug' => 'swimming', 'is_active' => true],
                        ]
                    ]
                ]
            ],

            // السيارات وقطع الغيار
            [
                'name' => 'السيارات',
                'slug' => 'automotive',
                'description' => 'قطع غيار وإكسسوارات السيارات',
                'is_active' => true,
                'children' => [
                    [
                        'name' => 'قطع غيار',
                        'slug' => 'auto-parts',
                        'description' => 'قطع غيار للسيارات',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'محركات', 'slug' => 'engines', 'is_active' => true],
                            ['name' => 'فرامل', 'slug' => 'brakes', 'is_active' => true],
                            ['name' => 'إطارات', 'slug' => 'tires', 'is_active' => true],
                            ['name' => 'بطاريات', 'slug' => 'batteries', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'إكسسوارات السيارات',
                        'slug' => 'car-accessories',
                        'description' => 'إكسسوارات وتحسينات للسيارات',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'أنظمة صوت', 'slug' => 'audio-systems', 'is_active' => true],
                            ['name' => 'أغطية مقاعد', 'slug' => 'seat-covers', 'is_active' => true],
                            ['name' => 'منظفات السيارات', 'slug' => 'car-care', 'is_active' => true],
                        ]
                    ]
                ]
            ],

            // كتب ووسائل تعليمية
            [
                'name' => 'كتب ووسائل تعليمية',
                'slug' => 'books-education',
                'description' => 'كتب ومواد تعليمية',
                'is_active' => true,
                'children' => [
                    [
                        'name' => 'كتب',
                        'slug' => 'books',
                        'description' => 'كتب بمختلف المجالات',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'روايات', 'slug' => 'novels', 'is_active' => true],
                            ['name' => 'كتب علمية', 'slug' => 'science-books', 'is_active' => true],
                            ['name' => 'كتب تطوير الذات', 'slug' => 'self-development', 'is_active' => true],
                            ['name' => 'كتب الأطفال', 'slug' => 'children-books', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'وسائل تعليمية',
                        'slug' => 'educational-materials',
                        'description' => 'مواد ووسائل تعليمية',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'أدوات مكتبية', 'slug' => 'stationery', 'is_active' => true],
                            ['name' => 'ألعاب تعليمية', 'slug' => 'educational-toys-books', 'is_active' => true],
                        ]
                    ]
                ]
            ],

            // ألعاب وهوايات
            [
                'name' => 'ألعاب وهوايات',
                'slug' => 'toys-hobbies',
                'description' => 'ألعاب ومنتجات الهوايات',
                'is_active' => true,
                'children' => [
                    [
                        'name' => 'ألعاب الأطفال',
                        'slug' => 'kids-toys',
                        'description' => 'ألعاب للأطفال',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'ألعاب تعليمية', 'slug' => 'educational-toys', 'is_active' => true],
                            ['name' => 'دمى', 'slug' => 'dolls', 'is_active' => true],
                            ['name' => 'سيارات ألعاب', 'slug' => 'toy-cars', 'is_active' => true],
                        ]
                    ],
                    [
                        'name' => 'ألعاب الفيديو',
                        'slug' => 'video-games',
                        'description' => 'ألعاب فيديو وملحقاتها',
                        'is_active' => true,
                        'children' => [
                            ['name' => 'بلايستيشن', 'slug' => 'playstation', 'is_active' => true],
                            ['name' => 'إكس بوكس', 'slug' => 'xbox', 'is_active' => true],
                            ['name' => 'نينتندو', 'slug' => 'nintendo', 'is_active' => true],
                        ]
                    ]
                ]
            ]
        ];

        $this->createCategories($categories);
    }

    private function createCategories($categories, $parentId = null)
    {
        foreach ($categories as $category) {
            $children = $category['children'] ?? [];
            unset($category['children']);

            $newCategory = Category::create(array_merge($category, [
                'parent_id' => $parentId
            ]));

            if (!empty($children)) {
                $this->createCategories($children, $newCategory->id);
            }
        }
    }
}