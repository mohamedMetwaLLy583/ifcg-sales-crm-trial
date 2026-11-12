<?php
require __DIR__.'/verify-media.php';
App\Models\Product::where('sku','like','IFCG-DEMO-%')->update(['is_active'=>true]);
foreach(config('ifcg-product-details') as $slug=>$details){
 $response=http('GET','/product/'.$slug.'?lang=ar');
 check($response->getStatusCode()===200 && str_contains($response->getContent(),'data-lightbox') && str_contains($response->getContent(),'product-preparation'),'gallery and preparation: '.$slug);
 check(count($details['cooking'])===4 && count($details['gallery'])===2,'complete source references: '.$slug);
 foreach($details['gallery'] as $photo) check(is_file(public_path($photo['src'])) && getimagesize(public_path($photo['src']))!==false,'valid gallery asset: '.$photo['src']);
}
$r=http('GET','/shop?lang=ar&category=sweet-potatoes&q=مستقيمة');
$document=new DOMDocument();
@$document->loadHTML('<?xml encoding="UTF-8">'.$r->getContent());
$xpath=new DOMXPath($document);
$results=$xpath->query('//main//article[contains(concat(" ",normalize-space(@class)," ")," product-card ")]');
check($results->length===1 && str_contains($document->saveHTML($results->item(0)),'product/plain-sweet-potato'),'category and translated search combined');
$r=http('GET','/shop?q=NO-MATCH-IFCG-TEST');
check(str_contains($r->getContent(),'class="empty"'),'empty catalogue search renders');
$r=http('GET','/product/steak-fries?lang=en');
check(str_contains($r->getContent(),'2:30-3:30') && str_contains($r->getContent(),'185-190°C'),'specific cooking guidance is not replaced by shared defaults');
$r=http('GET','/product/plain-sweet-potato?lang=de');
check($r->getStatusCode()===200 && str_contains($r->getContent(),'Prepare &amp; serve'),'other locale uses readable English detail fallback');
echo "SUCCESS: $passed checks including product experience.\n";
