<?php

namespace App\Providers;
use Illuminate\Support\Facades\Gate;


use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Product;
use App\Policies\ProductPolicy;
use App\Models\order_item;
use App\Policies\orderitempolicies;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
        Gate::policy(Product::class,ProductPolicy::class);
        Gate::policy(order_item::class,orderitempolicies::class);
    }
   
}
