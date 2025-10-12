<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Observers\OrderObserver;
use App\Observers\OrderGroupObserver;


class AppServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     */
    public function register(): void {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        Order::observe(OrderObserver::class);
        OrderGroup::observe(OrderGroupObserver::class); // Регистрируем наблюдатель для OrderGroup
    }

}
