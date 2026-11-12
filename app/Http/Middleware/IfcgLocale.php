<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class IfcgLocale {
 public function handle(Request $request, Closure $next) {
  $locale = $request->query('lang', $request->session()->get('ifcg_locale', 'en'));
  if (!is_string($locale) || !array_key_exists($locale, config('ifcg.locales'))) $locale = 'en';
  app()->setLocale($locale);
  $request->session()->put('ifcg_locale', $locale);
  return $next($request);
 }
}

