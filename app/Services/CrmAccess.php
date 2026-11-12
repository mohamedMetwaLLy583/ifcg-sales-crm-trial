<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\DB;
class CrmAccess {
 public static function can(string $ability,?User $user=null):bool {
  $u=$user?:auth()->user();if(!$u||!$u->is_active)return false;
  if($u->hasRole('admin'))return true;
  foreach($u->getRoleNames() as $role)if(in_array($ability,config('crm.abilities.'.$role,[])))return true;
  return false;
 }
 public static function staff(?User $u):bool{return $u && $u->is_active && $u->hasAnyRole(array_keys(config('crm.roles')));}
 public static function need(string $ability):void{abort_unless(self::can($ability),403);}
 public static function ownerIds():array{
  $u=auth()->user();if(self::can('sales.all'))return User::pluck('id')->all();
  if(self::can('sales.team')&&$u->crm_team_id)return User::where('crm_team_id',$u->crm_team_id)->pluck('id')->all();
  return [$u->id];
 }
 public static function scope($query,string $column='owner_id'){
  if(self::can('sales.all'))return $query;
  $ids=self::ownerIds();
  return $query->where(fn($q)=>$q->whereIn($column,$ids)->when(self::can('sales.assign'),fn($q)=>$q->orWhereNull($column)));
 }
 public static function inquiry($inquiry):void{self::need('sales.read');$q=\App\Models\IfcgInquiry::query();if(!self::can('sales.all')){$ids=self::ownerIds();$q->where(fn($x)=>$x->whereIn('owner_id',$ids)->orWhere(function($y){if(self::can('sales.assign'))$y->whereNull('owner_id');else $y->whereRaw('1=0');}));}abort_unless($q->whereKey($inquiry->id)->exists(),404);}
 public static function accounts($query){if(self::can('campaigns.read'))return $query;return self::scope($query);}
 public static function order($order):void{
  self::need('orders.read');if(self::can('orders.write')||self::can('finance.read')||self::can('sales.all'))return;
  abort_unless(self::scope(\App\Models\IfcgInquiry::query())->whereKey($order->inquiry_id)->exists(),404);
 }
 public static function salesOwners(){ $q=User::whereHas('roles',fn($r)=>$r->whereIn('name',['admin','sales_manager','sales'])->where('guard_name','web'))->where('is_active',true);if(!self::can('sales.all'))$q->whereIn('id',self::ownerIds());return $q->orderBy('name')->get(); }
 public static function audit(string $action,$model,array $changes=[]):void {
  unset($changes['password'],$changes['password_confirmation'],$changes['remember_token']);
  DB::table('crm_audits')->insert(['user_id'=>auth()->id(),'action'=>$action,'entity'=>$model->getTable(),'entity_id'=>$model->id,'changes'=>json_encode($changes,JSON_UNESCAPED_UNICODE),'created_at'=>now()]);
 }
}
