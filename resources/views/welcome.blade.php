<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-screen">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name') }}</title>
    <link href="https://fonts.bunny.net/css?family=urbanist:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Urbanist', ui-sans-serif, system-ui, sans-serif; }
        .dark body { background-color: #030712; color: #f9fafb; }
    </style>
    <script>
        (function () {
            const storageKey = 'theme';

            function storedTheme() {
                const v = localStorage.getItem(storageKey);
                if (v === 'light' || v === 'dark' || v === 'system') {
                    return v;
                }

                return 'system';
            }

            function resolvedDark() {
                const mode = storedTheme();
                if (mode === 'dark') {
                    return true;
                }
                if (mode === 'light') {
                    return false;
                }

                return window.matchMedia('(prefers-color-scheme: dark)').matches;
            }

            function applyHtmlClass() {
                document.documentElement.classList.toggle('dark', resolvedDark());
            }

            function syncThemeButtons() {
                const activeClasses = [
                    'text-blue-600', 'dark:text-blue-400',
                    'hover:text-blue-600', 'dark:hover:text-blue-400',
                ];
                const inactiveClasses = [
                    'text-gray-500', 'dark:text-gray-400',
                    'hover:text-gray-700', 'dark:hover:text-gray-300',
                ];

                document.querySelectorAll('[data-theme-toggle]').forEach(function (el) {
                    const mode = el.getAttribute('data-theme-toggle');
                    const active = mode === storedTheme();
                    el.setAttribute('aria-pressed', active ? 'true' : 'false');
                    activeClasses.forEach(function (c) {
                        el.classList.toggle(c, active);
                    });
                    inactiveClasses.forEach(function (c) {
                        el.classList.toggle(c, !active);
                    });

                    const iconWrap = el.querySelector('[data-theme-icon-wrap]');
                    if (iconWrap) {
                        const wrapActive = [
                            'ring-1', 'ring-inset', 'ring-blue-600/85', 'dark:ring-blue-400/85',
                        ];
                        wrapActive.forEach(function (c) {
                            iconWrap.classList.toggle(c, active);
                        });
                    }
                });
            }

            window.toggleTheme = function (mode) {
                localStorage.setItem(storageKey, mode);
                applyHtmlClass();
                syncThemeButtons();
            };

            applyHtmlClass();

            document.addEventListener('DOMContentLoaded', syncThemeButtons);

            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
                if (storedTheme() === 'system') {
                    applyHtmlClass();
                }
            });
        })();
    </script>
</head>
<body class="min-h-screen bg-gray-50 text-gray-950 antialiased dark:bg-gray-950 dark:text-white">

    <header id="header">
        <nav class="fixed z-20 w-full overflow-hidden border-b border-gray-100 dark:border-gray-900 backdrop-blur-2xl bg-white/80 dark:bg-gray-950/80">
            <div class="max-w-6xl px-6 m-auto">
                <div class="flex flex-wrap items-center justify-between py-2 sm:py-4">
                    <div class="flex items-center justify-between w-full lg:w-auto">
                        <span class="block text-xl font-black tracking-tight bg-gradient-to-r from-sky-400 via-blue-500 to-indigo-500 bg-clip-text text-transparent dark:from-sky-300 dark:via-blue-400 dark:to-indigo-400">
                            {{ config('app.name') }}
                        </span>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-3 lg:hidden" role="group" aria-label="{{ __('Theme') }}">
                                <button type="button" data-theme-toggle="light" onclick="toggleTheme('light')" class="rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-950 text-gray-500 dark:text-gray-400" title="{{ __('Light') }}" aria-label="{{ __('Light mode') }}">
                                    <span data-theme-icon-wrap class="inline-flex rounded-md p-1 transition-shadow duration-200">
                                        <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75ZM7.5 12a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM18.894 6.166a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061l1.591-1.59ZM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75ZM17.834 18.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 1 0-1.061 1.06l1.59 1.591ZM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18ZM7.758 17.303a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 0 1.06 1.061l1.591-1.59ZM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12ZM6.697 7.757a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 0 0-1.061 1.06l1.59 1.591Z" />
                                        </svg>
                                    </span>
                                </button>
                                <button type="button" data-theme-toggle="dark" onclick="toggleTheme('dark')" class="rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-950 text-gray-500 dark:text-gray-400" title="{{ __('Dark') }}" aria-label="{{ __('Dark mode') }}">
                                    <span data-theme-icon-wrap class="inline-flex rounded-md p-1 transition-shadow duration-200">
                                        <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 0 1 .162.819A8.97 8.97 0 0 0 9 6a9 9 0 0 0 9 9 8.97 8.97 0 0 0 3.463-.69.75.75 0 0 1 .981.98 10.503 10.503 0 0 1-9.694 6.46c-5.799 0-10.5-4.7-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 0 1 .818.162Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>
                                <button type="button" data-theme-toggle="system" onclick="toggleTheme('system')" class="rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-950 text-gray-500 dark:text-gray-400" title="{{ __('System') }}" aria-label="{{ __('Match system appearance') }}">
                                    <span data-theme-icon-wrap class="inline-flex rounded-md p-1 transition-shadow duration-200">
                                        <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M2.25 5.25a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3V15a3 3 0 0 1-3 3h-3v.257c0 .597.237 1.17.659 1.591l.621.622a.75.75 0 0 1-.53 1.28h-9a.75.75 0 0 1-.53-1.28l.621-.622a2.25 2.25 0 0 0 .659-1.59V18h-3a3 3 0 0 1-3-3V5.25Zm1.5 0v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>
                            </div>
                            <button type="button" id="menu-btn" class="block lg:hidden p-2 rounded-md text-gray-700 dark:text-gray-200" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div id="mobile-menu" class="hidden lg:flex w-full lg:w-auto flex-wrap justify-end items-center gap-4 py-4 lg:py-0">
                        <div class="hidden lg:flex items-center gap-3" role="group" aria-label="{{ __('Theme') }}">
                            <button type="button" data-theme-toggle="light" onclick="toggleTheme('light')" class="rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-950 text-gray-500 dark:text-gray-400" title="{{ __('Light') }}" aria-label="{{ __('Light mode') }}">
                                <span data-theme-icon-wrap class="inline-flex rounded-md p-1 transition-shadow duration-200">
                                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75ZM7.5 12a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM18.894 6.166a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061l1.591-1.59ZM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75ZM17.834 18.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 1 0-1.061 1.06l1.59 1.591ZM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18ZM7.758 17.303a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 0 1.06 1.061l1.591-1.59ZM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12ZM6.697 7.757a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 0 0-1.061 1.06l1.59 1.591Z" />
                                    </svg>
                                </span>
                            </button>
                            <button type="button" data-theme-toggle="dark" onclick="toggleTheme('dark')" class="rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-950 text-gray-500 dark:text-gray-400" title="{{ __('Dark') }}" aria-label="{{ __('Dark mode') }}">
                                <span data-theme-icon-wrap class="inline-flex rounded-md p-1 transition-shadow duration-200">
                                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 0 1 .162.819A8.97 8.97 0 0 0 9 6a9 9 0 0 0 9 9 8.97 8.97 0 0 0 3.463-.69.75.75 0 0 1 .981.98 10.503 10.503 0 0 1-9.694 6.46c-5.799 0-10.5-4.7-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 0 1 .818.162Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </button>
                            <button type="button" data-theme-toggle="system" onclick="toggleTheme('system')" class="rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-950 text-gray-500 dark:text-gray-400" title="{{ __('System') }}" aria-label="{{ __('Match system appearance') }}">
                                <span data-theme-icon-wrap class="inline-flex rounded-md p-1 transition-shadow duration-200">
                                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M2.25 5.25a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3V15a3 3 0 0 1-3 3h-3v.257c0 .597.237 1.17.659 1.591l.621.622a.75.75 0 0 1-.53 1.28h-9a.75.75 0 0 1-.53-1.28l.621-.622a2.25 2.25 0 0 0 .659-1.59V18h-3a3 3 0 0 1-3-3V5.25Zm1.5 0v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <div class="flex items-center gap-2 border-l border-gray-200 dark:border-gray-800 pl-4">
                            <a href="{{ route('filament.admin.auth.login') }}" class="px-3 py-2 text-sm font-semibold rounded-lg ring-1 ring-blue-600 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:ring-blue-500 dark:hover:bg-blue-500/10 transition">
                                {{ __('Log in') }}
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="hidden lg:inline-flex px-3 py-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-500 dark:bg-blue-500 dark:hover:bg-blue-400 transition shadow-sm">
                                    {{ __('Register') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="overflow-hidden">
        <section class="relative">
            <div class="relative pt-24 lg:pt-28">
                <div class="px-6 mx-auto max-w-7xl md:px-12">
                    <div class="text-center sm:mx-auto sm:w-10/12 lg:w-4/5 mx-auto">
                        <p class="mt-8 text-sm sm:text-base font-semibold leading-snug text-blue-600 dark:text-blue-400">
                            {{ __('Philippine National Public Key Infrastructure (PNPKI)') }}
                        </p>
                        <h1 class="mt-3 text-4xl md:text-5xl xl:text-5xl font-semibold text-gray-950 dark:text-gray-50" style="line-height:1.125">
                            {{ __('Submission Tracker') }}
                        </h1>
                        <p class="max-w-2xl mx-auto mt-8 text-lg text-gray-700 dark:text-gray-200">
                            {{ __('Track PNPKI employee applications, supporting IDs and documents, and office workflows from submission through review to resolution.') }}
                        </p>
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-8">
                            <a href="{{ route('filament.admin.auth.login') }}" class="px-5 py-3 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-500 dark:bg-blue-500 dark:hover:bg-blue-400 transition shadow-sm inline-flex items-center gap-2">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                                {{ __('Get started') }}
                            </a>
                            <a href="#features" class="px-5 py-3 text-sm font-semibold rounded-lg ring-1 ring-gray-300 dark:ring-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 transition inline-flex items-center gap-2">
                                {{ __('Learn more') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="features">
            <div class="pt-36">
                <div class="max-w-6xl px-6 mx-auto">
                    <div class="relative z-10 grid grid-cols-6 gap-3">

                        <div class="relative flex p-6 overflow-hidden border rounded-3xl dark:border-gray-800 col-span-full lg:col-span-2">
                            <div class="relative m-auto size-fit text-center">
                                <span class="block text-5xl font-semibold bg-gradient-to-r from-sky-400 via-blue-500 to-indigo-500 bg-clip-text text-transparent dark:from-sky-300 dark:via-blue-400 dark:to-indigo-400">100%</span>
                                <h2 class="mt-6 text-3xl font-semibold text-gray-800 dark:text-gray-50">{{ __('Transparent') }}</h2>
                                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Full visibility into every process.') }}</p>
                            </div>
                        </div>

                        <div class="relative p-6 overflow-hidden border rounded-3xl dark:border-gray-800 col-span-full sm:col-span-3 lg:col-span-2">
                            <div class="flex flex-col justify-between h-full space-y-6">
                                <div class="relative flex border rounded-full aspect-square size-12 text-blue-600 dark:text-blue-400 dark:bg-white/5 dark:border-white/10 items-center justify-center">
                                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                                        <path fill="none" stroke="currentColor" stroke-linejoin="round" d="M5.5 7c2 0 6.5-3 6.5-3s4.5 3 6.5 3v4.5C18.5 18 12 20 12 20s-6.5-2-6.5-8.5z"/>
                                    </svg>
                                </div>
                                <div class="space-y-2">
                                    <h2 class="text-lg font-medium text-gray-800 dark:text-white">{{ __('Unbreakable Workflow') }}</h2>
                                    <p class="text-gray-700 dark:text-gray-300">{{ __('No bottlenecks, no disruptions. A system designed to keep your requests moving forward.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="relative p-6 overflow-hidden border rounded-3xl dark:border-gray-800 col-span-full sm:col-span-3 lg:col-span-2">
                            <div class="flex flex-col justify-between h-full space-y-6">
                                <div class="relative flex border rounded-full aspect-square size-12 text-blue-600 dark:text-blue-400 dark:bg-white/5 dark:border-white/10 items-center justify-center">
                                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                                    </svg>
                                </div>
                                <div class="space-y-2">
                                    <h2 class="text-lg font-medium text-gray-800 dark:text-white">{{ __('Monitor Updates') }}</h2>
                                    <p class="text-gray-700 dark:text-gray-300">{{ __('Keep track of your requests and get notified of their progress in real time.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="relative p-6 border rounded-3xl dark:border-gray-800 col-span-full lg:col-span-3">
                            <h2 class="text-lg font-medium text-gray-800 dark:text-white mb-6">{{ __('Request Status Flow') }}</h2>
                            <div class="relative flex flex-col space-y-5 before:absolute before:w-px before:inset-0 before:mx-[1.25rem] before:bg-gray-300 dark:before:bg-gray-700">
                                <div class="flex items-center gap-3 pl-2">
                                    <div class="rounded-full size-8 ring-4 ring-gray-50 dark:ring-gray-950 p-1.5 bg-gray-50 dark:bg-gray-950 z-10">
                                        <svg class="text-blue-600 dark:text-blue-400 w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M5 4h14v2H5zm0 10h4v6h6v-6h4l-7-7-7 7zm8-2v6h-2v-6H9.83L12 9.83 14.17 12H13z"/></svg>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium text-white bg-gray-600 rounded-lg dark:bg-gray-300 dark:text-gray-800">{{ __('Submitted') }}</span>
                                </div>
                                <div class="flex items-center gap-3 pl-2">
                                    <div class="rounded-full size-8 ring-4 ring-gray-50 dark:ring-gray-950 p-1.5 bg-gray-50 dark:bg-gray-950 z-10">
                                        <svg class="text-blue-600 dark:text-blue-400 w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M7.34 6.41L.86 12.9l6.49 6.48 6.49-6.48-6.5-6.49zM3.69 12.9l3.66-3.66L11 12.9l-3.66 3.66-3.65-3.66zm15.67-6.26C17.61 4.88 15.3 4 13 4V.76L8.76 5 13 9.24V6c1.79 0 3.58.68 4.95 2.05 2.73 2.73 2.73 7.17 0 9.9C16.58 19.32 14.79 20 13 20c-.97 0-1.94-.21-2.84-.61l-1.49 1.49C10.02 21.62 11.51 22 13 22c2.3 0 4.61-.88 6.36-2.64 3.52-3.51 3.52-9.21 0-12.72z"/></svg>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium text-white bg-gray-600 rounded-lg dark:bg-gray-300 dark:text-gray-800">{{ __('Queued') }}</span>
                                </div>
                                <div class="flex items-center gap-3 pl-2">
                                    <div class="rounded-full size-8 ring-4 ring-gray-50 dark:ring-gray-950 p-1.5 bg-gray-50 dark:bg-gray-950 z-10">
                                        <svg class="text-blue-600 dark:text-blue-400 w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12c1.93 0 3.5-1.57 3.5-3.5S10.93 5 9 5 5.5 6.57 5.5 8.5 7.07 12 9 12zm0-5c.83 0 1.5.67 1.5 1.5S9.83 10 9 10s-1.5-.67-1.5-1.5S8.17 7 9 7zm.05 10H4.77c.99-.5 2.7-1 4.23-1 .11 0 .23.01.34.01.34-.73.93-1.33 1.64-1.81-.73-.13-1.42-.2-1.98-.2-2.34 0-7 1.17-7 3.5V19h7v-1.5c0-.17.02-.34.05-.5zm7.45-2.5c-1.84 0-5.5 1.01-5.5 3V19h11v-1.5c0-1.99-3.66-3-5.5-3zm1.21-1.82c.76-.43 1.29-1.24 1.29-2.18C19 9.12 17.88 8 16.5 8S14 9.12 14 10.5c0 .94.53 1.75 1.29 2.18.36.2.77.32 1.21.32s.85-.12 1.21-.32z"/></svg>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium text-white bg-gray-600 rounded-lg dark:bg-gray-300 dark:text-gray-800">{{ __('Assigned') }}</span>
                                </div>
                                <div class="flex items-center gap-3 pl-2">
                                    <div class="rounded-full size-8 ring-4 ring-blue-100 dark:ring-blue-950 p-1.5 bg-blue-50 dark:bg-blue-950 z-10">
                                        <svg class="text-blue-600 dark:text-blue-400 w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium text-white bg-blue-600 rounded-lg dark:bg-blue-500 dark:text-white">{{ __('Resolved') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="relative p-6 border rounded-3xl dark:border-gray-800 col-span-full lg:col-span-3 flex items-center justify-center">
                            <pre class="whitespace-pre-line text-center"><code class="font-mono text-sm text-gray-700 dark:text-gray-300">{{ __("Got a fresh idea? 🚀\n\nWe're always looking for ways to improve!\n\nSend us a suggestion, big or small,\nAnd we'll make sure to check it out!") }}</code></pre>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="pt-36 pb-24">
                <div class="max-w-6xl px-6 mx-auto">
                    <div class="text-center">
                        <h2 class="text-3xl font-semibold text-gray-950 dark:text-gray-50">
                            {{ __('Meet the') }} <span class="uppercase">{{ __('Team') }}</span>
                        </h2>
                        <p class="mt-6 text-gray-700 dark:text-gray-200">
                            {{ __('We are a team of developers passionate about building great software.') }}
                        </p>
                    </div>
                    <!-- <div class="flex flex-wrap justify-center gap-6 mt-12">
                        <div class="flex flex-col items-center gap-2">
                            <img src="https://ui-avatars.com/api/?name=John+Doe&size=120&rounded=true&background=2563eb&color=fff"
                                alt="{{ __('John') }}" class="rounded-full w-24 h-24 ring-2 ring-blue-400" loading="lazy" referrerpolicy="no-referrer">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">John Doe</span>
                        </div>
                        <div class="flex flex-col items-center gap-2">
                            <img src="https://ui-avatars.com/api/?name=Jane+Smith&size=120&rounded=true&background=2563eb&color=fff"
                                alt="{{ __('Jane') }}" class="rounded-full w-24 h-24 ring-2 ring-blue-400" loading="lazy" referrerpolicy="no-referrer">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Jane Smith</span>
                        </div>
                        <div class="flex flex-col items-center gap-2">
                            <img src="https://ui-avatars.com/api/?name=Alex+Cruz&size=120&rounded=true&background=2563eb&color=fff"
                                alt="{{ __('Alex') }}" class="rounded-full w-24 h-24 ring-2 ring-blue-400" loading="lazy" referrerpolicy="no-referrer">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Alex Cruz</span>
                        </div>
                    </div> -->
                </div>
            </div>
        </section>

        <section id="stack">
            <div class="py-36">
                <div class="max-w-6xl px-6 mx-auto">
                    <div class="text-center">
                        <h2 class="text-3xl font-semibold text-gray-950 dark:text-white">
                            {{ __('The tech') }} <span class="uppercase">{{ __('stack') }}</span>
                        </h2>
                        <p class="mt-6 text-gray-700 dark:text-gray-300">
                            {{ __('The technologies we use to build this application. We use the latest technologies to build this application.') }}
                        </p>
                    </div>
                    <div class="relative px-6 mt-12 -mx-6 overflow-x-auto w-fit h-fit sm:mx-auto sm:px-0">
                        <div class="flex gap-3 mx-auto mb-3 w-fit">
                            <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-10 *:m-auto size-20 mx-auto">
                                <svg class="fill-blue-600 dark:fill-blue-400" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>PHP</title><path d="M7.01 10.207h-.944l-.515 2.648h.838c.556 0 .97-.105 1.242-.314.272-.21.455-.559.55-1.049.092-.47.05-.802-.124-.995-.175-.193-.523-.29-1.047-.29zM12 5.688C5.373 5.688 0 8.514 0 12s5.373 6.313 12 6.313S24 15.486 24 12c0-3.486-5.373-6.312-12-6.312zm-3.26 7.451c-.261.25-.575.438-.917.551-.336.108-.765.164-1.285.164H5.357l-.327 1.681H3.652l1.23-6.326h2.65c.797 0 1.378.209 1.744.628.366.418.476 1.002.33 1.752a2.836 2.836 0 0 1-.305.847c-.143.255-.33.49-.561.703zm4.024.715l.543-2.799c.063-.318.039-.536-.068-.651-.107-.116-.336-.174-.687-.174H11.46l-.704 3.625H9.388l1.23-6.327h1.367l-.327 1.682h1.218c.767 0 1.295.134 1.586.401s.378.7.263 1.299l-.572 2.944h-1.389zm7.597-2.265a2.782 2.782 0 0 1-.305.847c-.143.255-.33.49-.561.703a2.44 2.44 0 0 1-.917.551c-.336.108-.765.164-1.286.164h-1.18l-.327 1.682h-1.378l1.23-6.326h2.649c.797 0 1.378.209 1.744.628.366.417.477 1.001.331 1.751zM17.766 10.207h-.943l-.516 2.648h.838c.557 0 .971-.105 1.242-.314.272-.21.455-.559.551-1.049.092-.47.049-.802-.125-.995s-.524-.29-1.047-.29z"/></svg>
                            </div>
                            <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-7 *:m-auto size-20 mx-auto">
                                <svg class="fill-blue-600 dark:fill-blue-400" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>Laravel</title><path d="M23.642 5.43a.364.364 0 01.014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934a.378.378 0 01-.188.326L9.93 23.949a.316.316 0 01-.066.027c-.008.002-.016.008-.024.01a.348.348 0 01-.192 0c-.011-.002-.02-.008-.03-.012-.02-.008-.042-.014-.062-.025L.533 18.755a.376.376 0 01-.189-.326V2.974c0-.033.005-.066.014-.098.003-.012.01-.02.014-.032a.369.369 0 01.023-.058c.004-.013.015-.022.023-.033l.033-.045c.012-.01.025-.018.037-.027.014-.012.027-.024.041-.034H.53L5.043.05a.375.375 0 01.375 0L9.93 2.647h.002c.015.01.027.021.04.033l.038.027c.013.014.02.03.033.045.008.011.02.021.025.033.01.02.017.038.024.058.003.011.01.021.013.032.01.031.014.064.014.098v9.652l3.76-2.164V5.527c0-.033.004-.066.013-.098.003-.01.01-.02.013-.032a.487.487 0 01.024-.059c.007-.012.018-.02.025-.033.012-.015.021-.03.033-.043.012-.012.025-.02.037-.028.014-.01.026-.023.041-.032h.001l4.513-2.598a.375.375 0 01.375 0l4.513 2.598c.016.01.027.021.042.031.012.01.025.018.036.028.013.014.022.03.034.044.008.012.019.021.024.033.011.02.018.04.024.06.006.01.012.021.015.032zm-.74 5.032V6.179l-1.578.908-2.182 1.256v4.283zm-4.51 7.75v-4.287l-2.147 1.225-6.126 3.498v4.325zM1.093 3.624v14.588l8.273 4.761v-4.325l-4.322-2.445-.002-.003H5.04c-.014-.01-.025-.021-.04-.031-.011-.01-.024-.018-.035-.027l-.001-.002c-.013-.012-.021-.025-.031-.04-.01-.011-.021-.022-.028-.036h-.002c-.008-.014-.013-.031-.02-.047-.006-.016-.014-.027-.018-.043a.49.49 0 01-.008-.057c-.002-.014-.006-.027-.006-.041V5.789l-2.18-1.257zM5.23.81L1.47 2.974l3.76 2.164 3.758-2.164zm1.956 13.505l2.182-1.256V3.624l-1.58.91-2.182 1.255v9.435zm11.581-10.95l-3.76 2.163 3.76 2.163 3.759-2.164zm-.376 4.978L16.21 7.087 14.63 6.18v4.283l2.182 1.256 1.58.908zm-8.65 9.654l5.514-3.148 2.756-1.572-3.757-2.163-4.323 2.489-3.941 2.27z"/></svg>
                            </div>
                            <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-7 *:m-auto size-20 mx-auto">
                                <svg class="fill-blue-600 dark:fill-blue-400" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>Livewire</title><path d="M12.001 0C6.1735 0 1.4482 4.9569 1.4482 11.0723c0 2.0888.5518 4.0417 1.5098 5.709.2492.2796.544.4843.9649.4843 1.3388 0 1.2678-2.0644 2.6074-2.0644 1.3395 0 1.4111 2.0644 2.75 2.0644 1.3388 0 1.2659-2.0644 2.6054-2.0644.5845 0 .9278.3967 1.2403.8398-.2213-.2055-.4794-.3476-.8203-.3476-1.1956 0-1.3063 1.6771-2.2012 2.1406v4.5097c0 .9145.7418 1.6563 1.6562 1.6563.9145 0 1.6563-.7418 1.6563-1.6563v-5.8925c.308.4332.647.8144 1.2207.8144 1.3388 0 1.266-2.0644 2.6055-2.0644.465 0 .7734.2552 1.039.58-.1294-.0533-.2695-.0878-.4297-.0878-1.1582 0-1.296 1.574-2.1171 2.0937v2.4356c0 .823.6672 1.4902 1.4902 1.4902s1.4902-.6672 1.4902-1.4902V16.371c.3234.4657.6684.8945 1.2774.8945.7955 0 1.093-.7287 1.4843-1.3203.6878-1.4704 1.0743-3.1245 1.0743-4.873C22.5518 4.9569 17.8284 0 12.001 0zm-.5664 2.877c2.8797 0 5.2148 2.7836 5.2148 5.8066 0 3.023-1.5455 5.1504-5.2148 5.1504-3.6693 0-5.2149-2.1274-5.2149-5.1504S8.5548 2.877 11.4346 2.877zM10.0322 4.537a1.9554 2.1583 0 00-1.955 2.1582 1.9554 2.1583 0 001.955 2.1582 1.9554 2.1583 0 001.9551-2.1582 1.9554 2.1583 0 00-1.955-2.1582zm-.3261.664a.9777.9961 0 01.9785.9962.9777.9961 0 01-.9785.996.9777.9961 0 01-.9766-.996.9777.9961 0 01.9766-.9961zM6.7568 15.6935c-1.0746 0-1.2724 1.3542-1.9511 1.9648v1.7813c0 .823.6672 1.4902 1.4902 1.4902s1.4902-.6672 1.4902-1.4902v-3.1817c-.2643-.3237-.5767-.5644-1.0293-.5644Z"/></svg>
                            </div>
                            <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-16 *:m-auto size-20 mx-auto">
                                <svg class="fill-blue-600 dark:fill-blue-400" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>Filament</title><path d="M2.074 9.603c-.404 0-.866.122-1.128.596-.086.15-.151.336-.197.558l-.084.41H.149l-.149.71h.515c-.17.812-.337 1.623-.506 2.435h.925c.172-.811.34-1.624.508-2.436h.849l.149-.71h-.888l.058-.295a.664.664 0 0 1 .18-.364c.1-.086.229-.13.36-.122.173.003.344.044.5.12l.224-.757a2.173 2.173 0 0 0-.8-.145Zm3.865.007-.944.082c-.319 1.54-.64 3.08-.962 4.62h.928c.021-.104.491-2.381.978-4.702Zm-2.046.022c-.288-.01-.605.136-.654.473-.13.663.559.652.874.481.115-.064.19-.188.227-.37.054-.21-.027-.584-.447-.584Zm19.43.347-.95.167-.213 1.02h-.5l-.15.71h.503c-.091.448-.185.895-.279 1.342-.056.267-.05.488.02.662.13.348.483.517.961.517.127-.003.473-.031.664-.151l.1-.772a.818.818 0 0 1-.46.154c-.42 0-.37-.409-.326-.617l.235-1.135h.923l.149-.71h-.925s.074-.348.248-1.187zM7.542 11.08c-.562.001-1.147.192-1.204.211l-.146.825c.2-.087.66-.264 1.077-.264a.93.93 0 0 1 .357.058.336.336 0 0 1 .217.411c-.902.148-1.15.172-1.342.238-.38.116-.665.375-.728.762-.084.389.03.726.292.918a.809.809 0 0 0 .493.147c.402.008.714-.216.9-.495h.072l-.04.42h.8c.143-.661.28-1.324.416-1.987.056-.256.047-.476-.028-.662-.197-.485-.735-.582-1.136-.582Zm3.958 0c-.142 0-.28.025-.41.076-.258.1-.482.273-.644.498h-.08l.067-.536-.783.047c-.215 1.049-.436 2.097-.655 3.145h.929l.133-.65c.095-.438.183-.878.273-1.317.301-.357.501-.406.646-.406.335 0 .34.33.278.627-.077.365-.15.731-.225 1.097-.044.217-.088.434-.134.65h.925c.77-3.66-.303 1.396.417-1.998.053-.064.117-.116.175-.174.108-.099.266-.203.447-.203.328 0 .343.317.279.628l-.36 1.746h.928c.135-.673.278-1.345.419-2.017.08-.368.054-.661-.074-.882-.128-.22-.35-.331-.668-.331-.142 0-.28.026-.412.077a1.492 1.492 0 0 0-.658.51h-.068a.708.708 0 0 0-.204-.404c-.13-.122-.31-.183-.541-.183Zm4.752 0c-.29 0-.547.063-.773.188-.633.352-.825 1.049-.878 1.324-.087.437-.114 1.182.57 1.581.25.146.56.22.932.22.428 0 .825-.103.978-.196l.124-.8c-.089.05-.186.091-.292.124-.464.144-1.192.182-1.404-.304a.706.706 0 0 1-.053-.313l.001-.012h1.9c.048-.12.073-.249.109-.373.07-.287.07-.54-.003-.756a.9.9 0 0 0-.416-.503c-.205-.12-.47-.18-.795-.18zm3.956 0c-.147 0-.288.025-.424.076a1.41 1.41 0 0 0-.654.498h-.076l.064-.536-.782.047c-.088.426-.292 1.421-.655 3.145h.928c.136-.656.273-1.311.407-1.967a2.03 2.03 0 0 1 .203-.208.95.95 0 0 1 .218-.144c.168-.08.476-.097.556.117.044.115.045.267.004.456l-.36 1.746h.928l.138-.654c.093-.455.188-.909.284-1.363.124-.59.004-1.213-.779-1.213zm-16.63.08-.453.016-.058.212a.08.08 0 0 0 .03.074l.314.219-.366.024c-.03.002-.053.026-.06.058l-.03.137a.08.08 0 0 0 .03.074l.314.218-.366.024c-.03.002-.053.026-.06.058l-.029.135a.08.08 0 0 0 .03.075l.314.218-.367.025c-.028.002-.053.025-.06.057-.011.046-.02.092-.029.138a.08.08 0 0 0 .03.073l.314.218-.366.025c-.03.002-.053.025-.06.058l-.03.137a.08.08 0 0 0 .032.073l.313.22-.367.023c-.028.002-.053.025-.06.057l-.127.504h.41l.065-.274.513-.045c.03-.002.054-.025.06-.058l.03-.136a.08.08 0 0 0-.031-.074l-.314-.219.367-.024c.029-.002.053-.026.06-.058.011-.045.019-.091.028-.136a.08.08 0 0 0-.03-.074l-.313-.219.366-.025c.03 0 .054-.025.06-.057l.03-.137a.08.08 0 0 0-.03-.074l-.315-.218.367-.025c.029-.002.053-.025.06-.057l.028-.128v-.009a.08.08 0 0 0-.03-.074l-.313-.218.367-.024c.029-.002.053-.025.06-.057l.028-.128v-.01a.08.08 0 0 0-.03-.073l-.415-.292Zm12.693.585c.168 0 .286.07.357.208a.67.67 0 0 1 .057.398l-1.122.024.01-.028c.122-.368.392-.6.698-.602zM7.728 12.75l-.105.504c-.173.256-.409.405-.633.408-.099.004-.359-.054-.305-.368a.374.374 0 0 1 .252-.286c.234-.089.519-.113.685-.196a.691.691 0 0 0 .106-.062Z"/></svg>
                            </div>
                        </div>
                        <div class="flex gap-3 mx-auto mb-3 w-fit">
                            <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-9 *:m-auto size-20 mx-auto">
                                <svg class="fill-blue-600 dark:fill-blue-400" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>Tailwind CSS</title><path d="M12.001,4.8c-3.2,0-5.2,1.6-6,4.8c1.2-1.6,2.6-2.2,4.2-1.8c0.913,0.228,1.565,0.89,2.288,1.624 C13.666,10.618,15.027,12,18.001,12c3.2,0,5.2-1.6,6-4.8c-1.2,1.6-2.6,2.2-4.2,1.8c-0.913-0.228-1.565-0.89-2.288-1.624 C16.337,6.182,14.976,4.8,12.001,4.8z M6.001,12c-3.2,0-5.2,1.6-6,4.8c1.2-1.6,2.6-2.2,4.2-1.8c0.913,0.228,1.565,0.89,2.288,1.624 c1.177,1.194,2.538,2.576,5.512,2.576c3.2,0,5.2-1.6,6-4.8c-1.2,1.6-2.6,2.2-4.2,1.8c-0.913-0.228-1.565-0.89-2.288-1.624 C10.337,13.382,8.976,12,6.001,12z"/></svg>
                            </div>
                            <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-7 *:m-auto size-20 mx-auto">
                                <svg class="fill-blue-600 dark:fill-blue-400" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>NGINX</title><path d="M12 0L1.605 6v12L12 24l10.395-6V6L12 0zm6 16.59c0 .705-.646 1.29-1.529 1.29-.631 0-1.351-.255-1.801-.81l-6-7.141v6.66c0 .721-.57 1.29-1.274 1.29H7.32c-.721 0-1.29-.6-1.29-1.29V7.41c0-.705.63-1.29 1.5-1.29.646 0 1.38.255 1.83.81l5.97 7.141V7.41c0-.721.6-1.29 1.29-1.29h.075c.72 0 1.29.6 1.29 1.29v9.18H18z"/></svg>
                            </div>
                            <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-7 *:m-auto size-20 mx-auto">
                                <svg class="fill-blue-600 dark:fill-blue-400" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>Docker</title><path d="M13.983 11.078h2.119a.186.186 0 00.186-.185V9.006a.186.186 0 00-.186-.186h-2.119a.185.185 0 00-.185.185v1.888c0 .102.083.185.185.185m-2.954-5.43h2.118a.186.186 0 00.186-.186V3.574a.186.186 0 00-.186-.185h-2.118a.185.185 0 00-.185.185v1.888c0 .102.082.185.185.185m0 2.716h2.118a.187.187 0 00.186-.186V6.29a.186.186 0 00-.186-.185h-2.118a.185.185 0 00-.185.185v1.887c0 .102.082.185.185.186m-2.93 0h2.12a.186.186 0 00.184-.186V6.29a.185.185 0 00-.185-.185H8.1a.185.185 0 00-.185.185v1.887c0 .102.083.185.185.186m-2.964 0h2.119a.186.186 0 00.185-.186V6.29a.185.185 0 00-.185-.185H5.136a.186.186 0 00-.186.185v1.887c0 .102.084.185.186.186m5.893 2.715h2.118a.186.186 0 00.186-.185V9.006a.186.186 0 00-.186-.186h-2.118a.185.185 0 00-.185.185v1.888c0 .102.082.185.185.185m-2.93 0h2.12a.185.185 0 00.184-.185V9.006a.185.185 0 00-.184-.186h-2.12a.185.185 0 00-.184.185v1.888c0 .102.083.185.185.185m-2.964 0h2.119a.185.185 0 00.185-.185V9.006a.185.185 0 00-.184-.186h-2.12a.186.186 0 00-.186.186v1.887c0 .102.084.185.186.185m-2.92 0h2.12a.185.185 0 00.184-.185V9.006a.185.185 0 00-.184-.186h-2.12a.185.185 0 00-.184.185v1.888c0 .102.082.185.185.185M23.763 9.89c-.065-.051-.672-.51-1.954-.51-.338.001-.676.03-1.01.087-.248-1.7-1.653-2.53-1.716-2.566l-.344-.199-.226.327c-.284.438-.49.922-.612 1.43-.23.97-.09 1.882.403 2.661-.595.332-1.55.413-1.744.42H.751a.751.751 0 00-.75.748 11.376 11.376 0 00.692 4.062c.545 1.428 1.355 2.48 2.41 3.124 1.18.723 3.1 1.137 5.275 1.137.983.003 1.963-.086 2.93-.266a12.248 12.248 0 003.823-1.389c.98-.567 1.86-1.288 2.61-2.136 1.252-1.418 1.998-2.997 2.553-4.4h.221c1.372 0 2.215-.549 2.68-1.009.309-.293.55-.65.707-1.046l.098-.288Z"/></svg>
                            </div>
                        </div>
                        <div class="flex gap-3 mx-auto mb-3 w-fit">
                            <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-10 *:m-auto size-20 mx-auto">
                                <svg class="fill-blue-600 dark:fill-blue-400" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>MySQL</title><path d="M16.405 5.501c-.115 0-.193.014-.274.033v.013h.014c.054.104.146.18.214.273.054.107.1.214.154.32l.014-.015c.094-.066.14-.172.14-.333-.04-.047-.046-.094-.08-.14-.04-.067-.126-.1-.18-.153zM5.77 18.695h-.927a50.854 50.854 0 00-.27-4.41h-.008l-1.41 4.41H2.45l-1.4-4.41h-.01a72.892 72.892 0 00-.195 4.41H0c.055-1.966.192-3.81.41-5.53h1.15l1.335 4.064h.008l1.347-4.064h1.095c.242 2.015.384 3.86.428 5.53zm4.017-4.08c-.378 2.045-.876 3.533-1.492 4.46-.482.716-1.01 1.073-1.583 1.073-.153 0-.34-.046-.566-.138v-.494c.11.017.24.026.386.026.268 0 .483-.075.647-.222.197-.18.295-.382.295-.605 0-.155-.077-.47-.23-.944L6.23 14.615h.91l.727 2.36c.164.536.233.91.205 1.123.4-1.064.678-2.227.835-3.483zm12.325 4.08h-2.63v-5.53h.885v4.85h1.745zm-3.32.135l-1.016-.5c.09-.076.177-.158.255-.25.433-.506.648-1.258.648-2.253 0-1.83-.718-2.746-2.155-2.746-.704 0-1.254.232-1.65.697-.43.508-.646 1.256-.646 2.245 0 .972.19 1.686.574 2.14.35.41.877.615 1.583.615.264 0 .506-.033.725-.098l1.325.772.36-.622zM15.5 17.588c-.225-.36-.337-.94-.337-1.736 0-1.393.424-2.09 1.27-2.09.443 0 .77.167.977.5.224.362.336.936.336 1.723 0 1.404-.424 2.108-1.27 2.108-.445 0-.77-.167-.978-.5zm-1.658-.425c0 .47-.172.856-.516 1.156-.344.3-.803.45-1.384.45-.543 0-1.064-.172-1.573-.515l.237-.476c.438.22.833.328 1.19.328.332 0 .593-.073.783-.22a.754.754 0 00.3-.615c0-.33-.23-.61-.648-.845-.388-.213-1.163-.657-1.163-.657-.422-.307-.632-.636-.632-1.177 0-.45.157-.81.47-1.085.315-.278.72-.415 1.22-.415.512 0 .98.136 1.4.41l-.213.476a2.726 2.726 0 00-1.064-.23c-.283 0-.502.068-.654.206a.685.685 0 00-.248.524c0 .328.234.61.666.85.393.215 1.187.67 1.187.67.433.305.648.63.648 1.168zm9.382-5.852c-.535-.014-.95.04-1.297.188-.1.04-.26.04-.274.167.055.053.063.14.11.214.08.134.218.313.346.407.14.11.28.216.427.31.26.16.555.255.81.416.145.094.293.213.44.313.073.05.12.14.214.172v-.02c-.046-.06-.06-.147-.105-.214-.067-.067-.134-.127-.2-.193a3.223 3.223 0 00-.695-.675c-.214-.146-.682-.35-.77-.595l-.013-.014c.146-.013.32-.066.46-.106.227-.06.435-.047.67-.106.106-.027.213-.06.32-.094v-.06c-.12-.12-.21-.283-.334-.395a8.867 8.867 0 00-1.104-.823c-.21-.134-.476-.22-.697-.334-.08-.04-.214-.06-.26-.127-.12-.146-.19-.34-.275-.514a17.69 17.69 0 01-.547-1.163c-.12-.262-.193-.523-.34-.763-.69-1.137-1.437-1.826-2.586-2.5-.247-.14-.543-.2-.856-.274-.167-.008-.334-.02-.5-.027-.11-.047-.216-.174-.31-.235-.38-.24-1.364-.76-1.644-.072-.18.434.267.862.422 1.082.115.153.26.328.34.5.047.116.06.235.107.356.106.294.207.622.347.897.073.14.153.287.247.413.054.073.146.107.167.227-.094.136-.1.334-.154.5-.24.757-.146 1.693.194 2.25.107.166.362.534.703.393.3-.12.234-.5.32-.835.02-.08.007-.133.048-.187v.015c.094.188.188.367.274.555.206.328.566.668.867.895.16.12.287.328.487.402v-.02h-.015c-.043-.058-.1-.086-.154-.133a3.445 3.445 0 01-.35-.4 8.76 8.76 0 01-.747-1.218c-.11-.21-.202-.436-.29-.643-.04-.08-.04-.2-.107-.24-.1.146-.247.273-.32.453-.127.288-.14.642-.188 1.01-.027.007-.014 0-.027.014-.214-.052-.287-.274-.367-.46-.2-.475-.233-1.238-.06-1.785.047-.14.247-.582.167-.716-.042-.127-.174-.2-.247-.303a2.478 2.478 0 01-.24-.427c-.16-.374-.24-.788-.414-1.162-.08-.173-.22-.354-.334-.513-.127-.18-.267-.307-.368-.52-.033-.073-.08-.194-.027-.274.014-.054.042-.075.094-.09.088-.072.335.022.422.062.247.1.455.194.662.334.094.066.195.193.315.226h.14c.214.047.455.014.655.073.355.114.675.28.962.46a5.953 5.953 0 012.085 2.286c.08.154.115.295.188.455.14.33.313.663.455.982.14.315.275.636.476.897.1.14.502.213.682.286.133.06.34.115.46.188.23.14.454.3.67.454.11.076.443.243.463.378z"/></svg>
                            </div>
                            <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-7 *:m-auto size-20 mx-auto">
                                <svg class="fill-blue-600 dark:fill-blue-400" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>SQLite</title><path d="M21.678.521c-1.032-.92-2.28-.55-3.513.544a8.71 8.71 0 0 0-.547.535c-2.109 2.237-4.066 6.38-4.674 9.544.237.48.422 1.093.544 1.561a13.044 13.044 0 0 1 .164.703s-.019-.071-.096-.296l-.05-.146a1.689 1.689 0 0 0-.033-.08c-.138-.32-.518-.995-.686-1.289-.143.423-.27.818-.376 1.176.484.884.778 2.4.778 2.4s-.025-.099-.147-.442c-.107-.303-.644-1.244-.772-1.464-.217.804-.304 1.346-.226 1.478.152.256.296.698.422 1.186.286 1.1.485 2.44.485 2.44l.017.224a22.41 22.41 0 0 0 .056 2.748c.095 1.146.273 2.13.5 2.657l.155-.084c-.334-1.038-.47-2.399-.41-3.967.09-2.398.642-5.29 1.661-8.304 1.723-4.55 4.113-8.201 6.3-9.945-1.993 1.8-4.692 7.63-5.5 9.788-.904 2.416-1.545 4.684-1.931 6.857.666-2.037 2.821-2.912 2.821-2.912s1.057-1.304 2.292-3.166c-.74.169-1.955.458-2.362.629-.6.251-.762.337-.762.337s1.945-1.184 3.613-1.72C21.695 7.9 24.195 2.767 21.678.521m-18.573.543A1.842 1.842 0 0 0 1.27 2.9v16.608a1.84 1.84 0 0 0 1.835 1.834h9.418a22.953 22.953 0 0 1-.052-2.707c-.006-.062-.011-.141-.016-.2a27.01 27.01 0 0 0-.473-2.378c-.121-.47-.275-.898-.369-1.057-.116-.197-.098-.31-.097-.432 0-.12.015-.245.037-.386a9.98 9.98 0 0 1 .234-1.045l.217-.028c-.017-.035-.014-.065-.031-.097l-.041-.381a32.8 32.8 0 0 1 .382-1.194l.2-.019c-.008-.016-.01-.038-.018-.053l-.043-.316c.63-3.28 2.587-7.443 4.8-9.791.066-.069.133-.128.198-.194Z"/></svg>
                            </div>
                        </div>
                        <div class="flex gap-3 mx-auto mb-3 w-fit">
                            <div class="border dark:border-gray-800 rounded-3xl flex relative *:relative *:size-8 *:m-auto size-20 mx-auto">
                                <svg class="fill-blue-600 dark:fill-blue-400" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>GitHub</title><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-gray-200 dark:border-gray-800 py-8">
            <div class="max-w-6xl px-6 mx-auto text-center text-sm text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
            </div>
        </footer>
    </main>

</body>
</html>
