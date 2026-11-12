<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class IfcgQuote extends Model {
 protected $guarded = ['id'];
 protected $casts = ['items'=>'array','customer_snapshot'=>'array','valid_until'=>'date','total_minor'=>'integer'];
 public function inquiry() { return $this->belongsTo(IfcgInquiry::class, 'inquiry_id'); }
}

