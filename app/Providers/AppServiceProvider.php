<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\Motorcycle;
use App\Services\Settings\ExchangeRate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ExchangeRate::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view): void {
            auth()->user()?->loadMissing('role.permissions');
            try {
                $view->with('usdVesRate', app(ExchangeRate::class)->current());
            } catch (\Throwable) {
                $view->with('usdVesRate', ExchangeRate::DEFAULT);
            }
        });

        View::composer(['partials.apply-modal', 'partials.docs-modal'], function ($view): void {
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
