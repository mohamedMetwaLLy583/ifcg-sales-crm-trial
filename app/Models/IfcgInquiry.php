<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class IfcgInquiry extends Model {
 protected $guarded = ['id'];
 protected $casts = ['items'=>'array', 'follow_up_at'=>'date'];
 public function quotes() { return $this->hasMany(IfcgQuote::class, 'inquiry_id'); }
 public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
 public function account() { return $this->belongsTo(CrmAccount::class, 'account_id'); }
}
