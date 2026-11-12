<?php
use App\Http\Controllers\IfcgController;
use App\Http\Middleware\IfcgLocale;
use Illuminate\Support\Facades\Route;
Route::middleware(IfcgLocale::class)->group(function() {
 Route::get('/dashboard',fn()=>redirect()->route('crm.home'));
 Route::get('/',[IfcgController::class,'home'])->name('ifcg.home');
 Route::get('/our-products',fn()=>redirect()->route('ifcg.catalog'))->name('ifcg.products.alias');
 Route::get('/{slug}',[IfcgController::class,'page'])->where('slug','company-profile|certificates|factory|our-brands|export|local-sales|catalogues|articles|contact-us|privacy-policy')->name('ifcg.page');
 Route::get('/shop',[IfcgController::class,'catalog'])->name('ifcg.catalog');
 Route::get('/product/{slug}',[IfcgController::class,'product'])->name('ifcg.product');
 Route::get('/request-quote',[IfcgController::class,'requestForm'])->name('ifcg.request');
 Route::post('/request-quote',[IfcgController::class,'submit'])->middleware('throttle:10,1')->name('ifcg.submit');
 Route::get('/login',[IfcgController::class,'loginForm'])->middleware('guest')->name('login');
 Route::post('/login',[IfcgController::class,'login'])->middleware(['guest','throttle:10,1'])->name('ifcg.login.post');
 Route::post('/logout',[IfcgController::class,'logout'])->middleware('auth')->name('logout');
 Route::middleware(['auth',\App\Http\Middleware\CrmStaff::class])->group(function() {
  Route::get('/sales/banners',[\App\Http\Controllers\IfcgSlideController::class,'index'])->name('ifcg.slides')->middleware(\App\Http\Middleware\CrmPermission::class.':content');
  Route::post('/sales/banners',[\App\Http\Controllers\IfcgSlideController::class,'store'])->name('ifcg.slides.store')->middleware(\App\Http\Middleware\CrmPermission::class.':content');
  Route::patch('/sales/banners/{slide}',[\App\Http\Controllers\IfcgSlideController::class,'update'])->name('ifcg.slides.update')->middleware(\App\Http\Middleware\CrmPermission::class.':content');
  Route::delete('/sales/banners/{slide}',[\App\Http\Controllers\IfcgSlideController::class,'disable'])->name('ifcg.slides.disable')->middleware(\App\Http\Middleware\CrmPermission::class.':content');
  Route::get('/sales/products',[\App\Http\Controllers\IfcgProductController::class,'index'])->name('ifcg.manage')->middleware(\App\Http\Middleware\CrmPermission::class.':catalog');
  Route::get('/sales/products/{product}/edit',[\App\Http\Controllers\IfcgProductController::class,'edit'])->name('ifcg.product.edit')->middleware(\App\Http\Middleware\CrmPermission::class.':catalog');
  Route::patch('/sales/products/{product}',[\App\Http\Controllers\IfcgProductController::class,'update'])->name('ifcg.product.update')->middleware(\App\Http\Middleware\CrmPermission::class.':catalog');
  Route::get('/sales',[IfcgController::class,'sales'])->name('ifcg.sales');
  Route::get('/sales/inquiries/{inquiry}',[IfcgController::class,'inquiry'])->name('ifcg.inquiry');
  Route::patch('/sales/inquiries/{inquiry}',[IfcgController::class,'update'])->name('ifcg.inquiry.update');
  Route::post('/sales/inquiries/{inquiry}/quotes',[IfcgController::class,'createQuote'])->name('ifcg.quote.create');
  Route::get('/sales/quotes/{quote}',[IfcgController::class,'quote'])->name('ifcg.quote');
  Route::patch('/sales/quotes/{quote}',[IfcgController::class,'quoteStatus'])->name('ifcg.quote.status');
 });
});

