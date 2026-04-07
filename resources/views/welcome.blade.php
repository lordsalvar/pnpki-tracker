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
                    <div class="flex flex-wrap justify-center gap-6 mt-12">
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
