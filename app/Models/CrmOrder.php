<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CrmOrder extends Model { protected $guarded=['id']; protected $casts=['due_at'=>'date','payment_due_at'=>'date']; public function quote(){return $this->belongsTo(IfcgQuote::class,'quote_id');} public function inquiry(){return $this->belongsTo(IfcgInquiry::class,'inquiry_id');} public function owner(){return $this->belongsTo(User::class,'owner_id');} }
