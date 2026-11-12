<?php
namespace App\Http\Middleware;
class CrmPermission {
 public function handle($request,\Closure $next,string $ability){\App\Services\CrmAccess::need($ability);return $next($request);}
}
