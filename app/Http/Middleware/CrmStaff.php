<?php
namespace App\Http\Middleware;
use Closure;
use App\Services\CrmAccess;
class CrmStaff {
 public function handle($request,Closure $next){
  $u=$request->user();abort_unless($u,401);$u->refresh();
  $key='crm_session_version_'.$u->id;
  if(!CrmAccess::staff($u)||($request->session()->has($key)&&(int)$request->session()->get($key)!==(int)$u->session_version)){
   auth()->logout();$request->session()->invalidate();$request->session()->regenerateToken();abort(403,'الحساب غير متاح. سجّل الدخول مجددًا أو تواصل مع الإدارة.');
  }
  $request->session()->put($key,$u->session_version);return $next($request);
 }
}
