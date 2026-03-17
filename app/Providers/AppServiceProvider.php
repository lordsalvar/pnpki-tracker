<?php

namespace App\Providers;

use App\Models\Form;
use App\Policies\FormPolicy;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
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

        Gate::policy(Form::class, FormPolicy::class);
    }
}
