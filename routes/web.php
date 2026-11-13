<?php
require __DIR__.'/ifcg.php';
require __DIR__.'/crm.php';
require __DIR__.'/ifcg-legacy.php';
\Illuminate\Support\Facades\Route::get('/health', static function () {
    \Illuminate\Support\Facades\DB::connection()->getPdo();

    return response()->json(['status' => 'ok']);
});
