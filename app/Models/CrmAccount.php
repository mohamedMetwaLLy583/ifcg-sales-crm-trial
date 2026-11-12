<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CrmAccount extends Model { protected $guarded=['id']; protected $casts=['archived'=>'boolean']; public function owner(){return $this->belongsTo(User::class,'owner_id');} public function contacts(){return $this->hasMany(CrmContact::class,'account_id');} public function inquiries(){return $this->hasMany(IfcgInquiry::class,'account_id');} }
