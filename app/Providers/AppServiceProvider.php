<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\Motorcycle;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('*', function (): void {
            auth()->user()?->loadMissing('role.permissions');
        });

        View::composer('partials.apply-modal', function ($view): void {
            $view->with(
                'applyMotorcycles',
                Motorcycle::query()->where('is_active', true)->orderBy('sort_order')->get(),
            );
            $view->with(
                'applyBranches',
                Branch::query()->where('is_active', true)->orderBy('city')->get(),
            );
        });
    }
}
