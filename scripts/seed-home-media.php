<?php
require __DIR__.'/../vendor/autoload.php';$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(!App\Models\IfcgSlide::exists())App\Models\IfcgSlide::create(['media_path'=>'/ifcg/images/pages/factory-1.jpg','media_type'=>'image','button_url'=>'/request-quote','sort_order'=>0,'is_active'=>true]);
if(!App\Models\IfcgSlide::where('media_path','/ifcg/images/official-production-4k.webm')->exists())App\Models\IfcgSlide::create(['media_path'=>'/ifcg/images/official-production-4k.webm','media_type'=>'video','poster_path'=>'/ifcg/images/pages/factory-1.jpg','button_url'=>'/request-quote','sort_order'=>1,'is_active'=>true]);
echo json_encode(['slides'=>App\Models\IfcgSlide::count(),'image'=>getimagesize(public_path('ifcg/images/pages/factory-1.jpg')),'upload'=>ini_get('upload_max_filesize'),'post'=>ini_get('post_max_size')]);
