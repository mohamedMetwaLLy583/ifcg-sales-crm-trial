<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        \Illuminate\Support\Facades\View::share('access',\App\Services\CrmAccess::class);
        Paginator::useBootstrapFive();
        Order::observe(OrderObserver::class);

        $uploadPath = public_path('uploads');
        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);

            File::makeDirectory($uploadPath . '/profile_images', 0755, true);
            File::makeDirectory($uploadPath . '/documents', 0755, true);
            File::makeDirectory($uploadPath . '/temporary', 0755, true);

            File::put($uploadPath . '/profile_images/.gitkeep', '');
            File::put($uploadPath . '/documents/.gitkeep', '');
            File::put($uploadPath . '/temporary/.gitkeep', '');
        }
    }
}
