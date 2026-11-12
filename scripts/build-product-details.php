<?php
$root=dirname(__DIR__);
$refs=json_decode(file_get_contents($root.'/data/product-reference.json'),true);
$copy=[
'crinkle-fries'=>['قطع مموجة تمنح أطباقك حضورًا مختلفًا، مع سطح يناسب الصوصات والإضافات. اختيار للأطباق الجانبية ووجبات الأطفال وأطباق البطاطس المحمّلة.','Ridged fries for dipping, side dishes and loaded fries. Their wavy surface brings texture and helps carry sauces and toppings.'],
'plain-sweet-potato'=>['بطاطا حلوة بقطع مستقيمة 10×10 مم ونكهة حلوة طبيعية. أضفها كطبق جانبي مختلف أو قدّمها كوجبة خفيفة مع الصوص المفضل.','Naturally sweet, straight-cut fries in a 10×10 mm format. Serve as a distinctive side or a snack with a choice of dips.'],
'crinkle-sweet-potato'=>['بطاطا حلوة بقطع مموجة 11×11 مم، تجمع بين النكهة الحلوة الطبيعية والشكل المميز. مناسبة لتنويع قوائم المطاعم والمقاهي والأطباق الجانبية.','Naturally sweet fries with an 11×11 mm crinkle cut. A distinctive shape for restaurant sides, café menus and shared snacks.'],
'wedges'=>['قطع ويدجز متبلة بقشرتها، بطابع ريفي وقوام سميك. قدّمها مع البرجر والمشويات أو كطبق مشاركة مع التغميسات.','Seasoned, skin-on potato wedges with a hearty rustic cut. Pair with burgers, grilled dishes or dipping sauces.'],
'crispy-cut-waffle-potatoes'=>['بطاطس وافل بدون قشرة، بتقطيع شبكي يلفت الانتباه على الطبق. مناسبة للمقبلات والأطباق الجانبية والتقديم مع الإضافات والصوصات.','Skin-off waffle fries with a distinctive crisscross shape. Use for appetizers, side dishes or creative toppings.'],
'crinkle-sliced'=>['شرائح بطاطس عريضة ومموجة، بمساحة أكبر لحمل الجبن والصوصات والتوابل. اختيار لأطباق المشاركة والبطاطس المحمّلة والإضافات الجانبية.','Wide, ridged potato slices for sauces, cheese and seasoning. A substantial option for loaded fries and sharing dishes.'],
'steak-fries'=>['قطع بطاطس سميكة 13×13 مم، بقلب طري وقوام ممتلئ. تناسب أطباق الستيك والبرجر والتقديم مع الصوصات والإضافات.','Thick 13×13 mm fries with a soft centre and a substantial bite. Pair with steak, burgers or loaded toppings.'],
'allumettes'=>['قطع بطاطس رفيعة 7×7 مم، مناسبة لقوائم الوجبات السريعة والمطابخ التي تحتاج إلى تقديم سريع. شكل خفيف يكمّل الوجبات والسندوتشات.','Slim 7×7 mm fries suited to quick-service menus and fast-paced kitchens. A light-cut side for meals and sandwiches.'],
'pommes-frites'=>['قطع البطاطس الكلاسيكية 10×10 مم، لاستخدامات متعددة في المطاعم والتجزئة. قدّمها كطبق جانبي أو وجبة خفيفة أو كأساس للإضافات.','Classic 10×10 mm fries for foodservice and retail. Serve as a familiar side, a snack or a base for loaded fries.'],
'premium-clear-coated'=>['بطاطس مغطاة بطبقة مقرمشة، مقلية مبدئيًا ومجمدة سريعًا. مناسبة للمطاعم والمقاهي والوجبات السريعة التي تبحث عن قرمشة مميزة وسهولة التحضير.','Coated, pre-fried and quick-frozen fries for a crisp exterior. A convenient choice for restaurants, cafés and quick-service menus.']];
$cuts=['plain-sweet-potato'=>'10 × 10','crinkle-sweet-potato'=>'11 × 11','steak-fries'=>'13 × 13','allumettes'=>'7 × 7','pommes-frites'=>'10 × 10'];
$out=[];
foreach($refs as $slug=>$r){
 $text=implode(' ',$r['paragraphs']);preg_match('/Shelf Life: (\d+) months/',$text,$life);
 $cooking=array_values(array_filter($r['paragraphs'],fn($p)=>str_contains($p,' mins')));
 $out[$slug]=['source'=>'https://ifcgegypt.com/product/'.$slug.'/','description'=>['ar'=>$copy[$slug][0],'en'=>$copy[$slug][1]],'shelf_months'=>(int)($life[1]??0),'cut'=>$cuts[$slug]??null,'cooking'=>$cooking,'gallery'=>[]];
 foreach($r['images'] as $i=>$url){$ext=pathinfo(parse_url($url,PHP_URL_PATH),PATHINFO_EXTENSION);$out[$slug]['gallery'][]=['src'=>'/ifcg/images/products/'.$slug.'-'.($i+1).'.'.$ext,'source'=>$url];}
}
file_put_contents($root.'/data/product-details.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
echo count($out)." product references prepared.\n";
