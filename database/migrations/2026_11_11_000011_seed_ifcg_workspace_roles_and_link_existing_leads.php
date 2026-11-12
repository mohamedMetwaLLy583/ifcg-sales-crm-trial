<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
return new class extends Migration {
 public function up(){
  $roles=['executive','sales_manager','sales','marketing','operations','finance','system_admin'];
  foreach($roles as $name) DB::table('roles')->updateOrInsert(['name'=>$name,'guard_name'=>'web'],['updated_at'=>now(),'created_at'=>now()]);
  DB::table('crm_teams')->insertOrIgnore([['name'=>'المبيعات المحلية','created_at'=>now(),'updated_at'=>now()],['name'=>'التصدير','created_at'=>now(),'updated_at'=>now()]]);
  if(!Schema::hasTable('ifcg_inquiries')) return;
  foreach(DB::table('ifcg_inquiries')->whereNull('account_id')->orderBy('id')->cursor() as $i){
   $email=mb_strtolower(trim((string)$i->email));$name=trim((string)$i->company);$country=trim((string)$i->country);$key=hash('sha256',mb_strtolower($name).'|'.mb_strtolower($country).'|'.$email);
   $account=DB::table('crm_accounts')->where('dedupe_key',$key)->first();
   if(!$account){$id=DB::table('crm_accounts')->insertGetId(['name'=>$name?:'عميل بدون اسم','country'=>$country?:'غير محدد','segment'=>'importer','email'=>$email?:'unknown-'.$i->id.'@ifcg.local','phone'=>$i->phone,'dedupe_key'=>$key,'source'=>'import','owner_id'=>$i->owner_id,'created_at'=>$i->created_at,'updated_at'=>$i->updated_at]);$account=(object)['id'=>$id];}
   DB::table('ifcg_inquiries')->where('id',$i->id)->update(['account_id'=>$account->id,'source'=>'website']);
  }
 }
 public function down(){}
};
