<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$slide=App\Models\IfcgSlide::firstOrCreate(['media_path'=>'/ifcg/images/harvest-illustration.png'],[
 'media_type'=>'image','button_url'=>'/shop','sort_order'=>-1,'is_active'=>true,
]);
echo 'Illustrated slide ready: '.$slide->id.PHP_EOL;
