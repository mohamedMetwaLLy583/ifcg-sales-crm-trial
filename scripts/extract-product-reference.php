<?php
$result=[];
foreach(glob(__DIR__.'/../data/product-sources/*.html') as $file){
 $doc=new DOMDocument(); @$doc->loadHTML(file_get_contents($file)); $xp=new DOMXPath($doc);
 $summary=$xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," summary ")]')->item(0);
 $paras=[]; if($summary)foreach($xp->query('.//p',$summary) as $p){$t=trim(preg_replace('/\s+/u',' ',$p->textContent));if($t && !str_contains($t,'First Name:') && !str_contains($t,'Company Name:'))$paras[]=$t;}
 $images=[];foreach($xp->query('//*[@data-large_image]') as $img)$images[]=$img->getAttribute('data-large_image');
 $result[pathinfo($file,PATHINFO_FILENAME)]=['paragraphs'=>$paras,'images'=>array_values(array_unique($images))];
}
file_put_contents(__DIR__.'/../data/product-reference.json',json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
