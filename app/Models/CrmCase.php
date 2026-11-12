<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CrmCase extends Model { protected $guarded=['id']; protected $casts=['due_at'=>'date']; public function inquiry(){return $this->belongsTo(IfcgInquiry::class,'inquiry_id');} }
