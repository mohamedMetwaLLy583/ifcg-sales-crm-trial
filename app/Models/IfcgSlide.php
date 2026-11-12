<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class IfcgSlide extends Model {
 protected $fillable=['locale','title','description','button_text','button_url','media_path','media_type','poster_path','sort_order','is_active'];
 protected $casts=['is_active'=>'boolean','sort_order'=>'integer'];
 public function scopeVisible($query,string $locale){return $query->where('is_active',true)->where(fn($q)=>$q->whereNull('locale')->orWhere('locale',$locale))->orderBy('sort_order')->orderBy('id');}
}
