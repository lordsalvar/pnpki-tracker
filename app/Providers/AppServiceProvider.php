<?php

namespace App\Providers;

use App\Models\Batch;
use App\Models\EmployeeForm;
use App\Policies\BatchPolicy;
use App\Policies\EmployeeFormPolicy;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn () => Blade::render('@vite(\'resources/css/app.css\')')
        );

        Gate::policy(EmployeeForm::class, EmployeeFormPolicy::class);
        Gate::policy(Batch::class, BatchPolicy::class);

        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }
}
