<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CrmTask extends Model { protected $guarded=['id']; protected $casts=['due_at'=>'datetime']; public function owner(){return $this->belongsTo(User::class,'owner_id');} public function inquiry(){return $this->belongsTo(IfcgInquiry::class,'inquiry_id');} }
