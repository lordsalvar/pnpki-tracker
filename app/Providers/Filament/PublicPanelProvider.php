<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PublicPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('public')
            ->path('p')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Public/Resources'), for: 'App\Filament\Public\Resources')
            ->discoverPages(in: app_path('Filament/Public/Pages'), for: 'App\Filament\Public\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Public/Widgets'), for: 'App\Filament\Public\Widgets')
            ->widgets([])
            ->navigation(false)
            ->renderHook(
                'panels::body.end',
                fn () => '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>'
            )
            ->brandLogo(fn (): HtmlString => new HtmlString(
                '<span class="block mx-auto text-xl font-black tracking-tight text-transparent w-fit bg-clip-text bg-gradient-to-r from-sky-400 via-blue-500 to-indigo-500 dark:from-sky-300 dark:via-blue-400 dark:to-indigo-400">'
                .e(config('app.name'))
                .'</span>'
            ))
            ->brandLogoHeight('1.5rem')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ]);
    }
}
