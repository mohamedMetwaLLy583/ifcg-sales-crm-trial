<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$path = '/ifcg/images/official-production-4k.webm';
if (!is_file(public_path($path)) || filesize(public_path($path)) !== 14482718) {
    throw new RuntimeException('The complete official 4K asset is required.');
}
$updated = App\Models\IfcgSlide::where('media_path', '/ifcg/images/official-factory.webm')
    ->update(['media_path' => $path]);
echo "Updated {$updated} official video slide(s); custom slides preserved.\n";
