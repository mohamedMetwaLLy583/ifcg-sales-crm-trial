<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CrmCampaign extends Model { protected $guarded=['id']; protected $casts=['starts_at'=>'date','ends_at'=>'date']; }
