<?php

namespace App\Providers;

use App\Models\Batch;
use App\Models\EmployeeForm;
use App\Policies\BatchPolicy;
use App\Policies\EmployeeFormPolicy;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Http\Request;
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

        $appUrl = rtrim((string) config('app.url'), '/');

        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
            URL::forceRootUrl($appUrl);
        }

        if (! $this->app->environment('local', 'testing')) {
            $this->registerAlternateSignedUrlValidation();
        }
    }

    /**
     * Accept signed URLs when the proxy terminates TLS (http vs https mismatch).
     */
    private function registerAlternateSignedUrlValidation(): void
    {
        Request::macro('hasValidSignature', function (bool $absolute = true, array $ignoreQuery = []) {
            /** @var Request $this */
            if (URL::hasValidSignature($this, absolute: $absolute, ignoreQuery: $ignoreQuery)) {
                return true;
            }

            $alternate = $this->duplicate();

            if ($this->isSecure()) {
                $alternate->server->set('HTTPS', 'off');
                $alternate->server->set('REQUEST_SCHEME', 'http');
            } else {
                $alternate->server->set('HTTPS', 'on');
                $alternate->server->set('REQUEST_SCHEME', 'https');
            }

            return URL::hasValidSignature($alternate, absolute: $absolute, ignoreQuery: $ignoreQuery);
        });
    }
}
