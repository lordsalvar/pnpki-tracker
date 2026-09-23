<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-screen">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name') }}</title>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
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
<body class="min-h-screen bg-stone-50 text-stone-950 antialiased dark:bg-stone-950 dark:text-stone-50">

    <header>
        <nav class="fixed z-20 w-full border-b border-stone-200/80 bg-stone-50/85 backdrop-blur-xl dark:border-stone-800 dark:bg-stone-950/85">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-3">
                <div class="flex w-full items-center justify-between lg:w-auto">
                    <a href="{{ url('/') }}" class="flex items-baseline gap-2">
                        <span class="text-sm font-semibold tracking-[0.18em] text-stone-950 uppercase dark:text-stone-50">PNPKI</span>
                        <span class="text-sm text-stone-500 dark:text-stone-400">Tracker</span>
                    </a>
                    <div class="flex items-center gap-2 lg:hidden">
                        <div class="flex items-center gap-2" role="group" aria-label="{{ __('Theme') }}">
                            <button type="button" data-theme-toggle="light" onclick="toggleTheme('light')" class="rounded-md text-gray-500 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 dark:text-gray-400" title="{{ __('Light') }}" aria-label="{{ __('Light mode') }}">
                                <span data-theme-icon-wrap class="inline-flex rounded-md p-1">
                                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75ZM7.5 12a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM18.894 6.166a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061l1.591-1.59ZM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75ZM17.834 18.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 1 0-1.061 1.06l1.59 1.591ZM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18ZM7.758 17.303a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 0 1.06 1.061l1.591-1.59ZM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12ZM6.697 7.757a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 0 0-1.061 1.06l1.59 1.591Z" />
                                    </svg>
                                </span>
                            </button>
                            <button type="button" data-theme-toggle="dark" onclick="toggleTheme('dark')" class="rounded-md text-gray-500 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 dark:text-gray-400" title="{{ __('Dark') }}" aria-label="{{ __('Dark mode') }}">
                                <span data-theme-icon-wrap class="inline-flex rounded-md p-1">
                                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 0 1 .162.819A8.97 8.97 0 0 0 9 6a9 9 0 0 0 9 9 8.97 8.97 0 0 0 3.463-.69.75.75 0 0 1 .981.98 10.503 10.503 0 0 1-9.694 6.46c-5.799 0-10.5-4.7-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 0 1 .818.162Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </button>
                            <button type="button" data-theme-toggle="system" onclick="toggleTheme('system')" class="rounded-md text-gray-500 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 dark:text-gray-400" title="{{ __('System') }}" aria-label="{{ __('Match system appearance') }}">
                                <span data-theme-icon-wrap class="inline-flex rounded-md p-1">
                                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M2.25 5.25a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3V15a3 3 0 0 1-3 3h-3v.257c0 .597.237 1.17.659 1.591l.621.622a.75.75 0 0 1-.53 1.28h-9a.75.75 0 0 1-.53-1.28l.621-.622a2.25 2.25 0 0 0 .659-1.59V18h-3a3 3 0 0 1-3-3V5.25Zm1.5 0v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <button type="button" class="rounded-md p-2 text-stone-700 dark:text-stone-200" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" aria-label="{{ __('Menu') }}">
                            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="mobile-menu" class="hidden w-full flex-wrap items-center justify-end gap-6 lg:flex lg:w-auto">
                    <div class="flex flex-col gap-3 text-sm font-medium text-stone-600 lg:flex-row lg:items-center lg:gap-6 dark:text-stone-300">
                        <a href="#pipeline" class="hover:text-stone-950 dark:hover:text-white">{{ __('Pipeline') }}</a>
                        <a href="#roles" class="hover:text-stone-950 dark:hover:text-white">{{ __('Roles') }}</a>
                        <a href="#control" class="hover:text-stone-950 dark:hover:text-white">{{ __('Control') }}</a>
                    </div>
                    <div class="hidden items-center gap-2 lg:flex" role="group" aria-label="{{ __('Theme') }}">
                        <button type="button" data-theme-toggle="light" onclick="toggleTheme('light')" class="rounded-md text-gray-500 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 dark:text-gray-400" title="{{ __('Light') }}" aria-label="{{ __('Light mode') }}">
                            <span data-theme-icon-wrap class="inline-flex rounded-md p-1">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75ZM7.5 12a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM18.894 6.166a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061l1.591-1.59ZM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75ZM17.834 18.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 1 0-1.061 1.06l1.59 1.591ZM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18ZM7.758 17.303a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 0 1.06 1.061l1.591-1.59ZM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12ZM6.697 7.757a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 0 0-1.061 1.06l1.59 1.591Z" />
                                </svg>
                            </span>
                        </button>
                        <button type="button" data-theme-toggle="dark" onclick="toggleTheme('dark')" class="rounded-md text-gray-500 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 dark:text-gray-400" title="{{ __('Dark') }}" aria-label="{{ __('Dark mode') }}">
                            <span data-theme-icon-wrap class="inline-flex rounded-md p-1">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 0 1 .162.819A8.97 8.97 0 0 0 9 6a9 9 0 0 0 9 9 8.97 8.97 0 0 0 3.463-.69.75.75 0 0 1 .981.98 10.503 10.503 0 0 1-9.694 6.46c-5.799 0-10.5-4.7-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 0 1 .818.162Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </button>
                        <button type="button" data-theme-toggle="system" onclick="toggleTheme('system')" class="rounded-md text-gray-500 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 dark:text-gray-400" title="{{ __('System') }}" aria-label="{{ __('Match system appearance') }}">
                            <span data-theme-icon-wrap class="inline-flex rounded-md p-1">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M2.25 5.25a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3V15a3 3 0 0 1-3 3h-3v.257c0 .597.237 1.17.659 1.591l.621.622a.75.75 0 0 1-.53 1.28h-9a.75.75 0 0 1-.53-1.28l.621-.622a2.25 2.25 0 0 0 .659-1.59V18h-3a3 3 0 0 1-3-3V5.25Zm1.5 0v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </button>
                    </div>
                    <a href="{{ route('filament.admin.auth.login') }}" class="inline-flex items-center justify-center rounded-md bg-stone-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-800 dark:bg-white dark:text-stone-950 dark:hover:bg-stone-200">
                        {{ __('Enter console') }}
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="relative overflow-hidden pt-28 pb-16 lg:pt-36 lg:pb-24">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(37,99,235,0.14),transparent_52%)] dark:bg-[radial-gradient(ellipse_at_top_left,rgba(59,130,246,0.18),transparent_50%)]"></div>
            <div class="relative mx-auto grid max-w-6xl items-start gap-12 px-6 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <p class="text-xs font-semibold tracking-[0.22em] text-blue-700 uppercase dark:text-blue-400">
                        {{ __('Philippine National Public Key Infrastructure') }}
                    </p>
                    <h1 class="mt-5 max-w-xl text-5xl font-semibold tracking-tight text-stone-950 sm:text-6xl dark:text-white" style="line-height:1.05">
                        {{ __('Nothing reaches DICT without a cleared record.') }}
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-stone-600 dark:text-stone-300">
                        {{ __('Employee certificate applications move from a public form through office review, batch lock, and admin release. Representatives own the office. Administrators authorize what leaves.') }}
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a href="{{ route('filament.admin.auth.login') }}" class="inline-flex items-center justify-center rounded-md bg-blue-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600 dark:bg-blue-500 dark:hover:bg-blue-400">
                            {{ __('Enter console') }}
                        </a>
                        <a href="#pipeline" class="inline-flex items-center justify-center rounded-md px-5 py-3 text-sm font-semibold text-stone-800 ring-1 ring-stone-300 transition hover:bg-white dark:text-stone-100 dark:ring-stone-700 dark:hover:bg-stone-900">
                            {{ __('Inspect the pipeline') }}
                        </a>
                    </div>
                </div>

                <aside class="lg:col-span-5">
                    <div class="rounded-2xl border border-stone-200 bg-white shadow-sm dark:border-stone-800 dark:bg-stone-900">
                        <div class="flex items-center justify-between border-b border-stone-200 px-5 py-4 dark:border-stone-800">
                            <p class="text-xs font-semibold tracking-[0.18em] text-stone-500 uppercase dark:text-stone-400">{{ __('Release gate') }}</p>
                            <span class="inline-flex items-center gap-2 text-xs font-medium text-emerald-700 dark:text-emerald-400">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                {{ __('Controlled') }}
                            </span>
                        </div>
                        <ol class="divide-y divide-stone-100 dark:divide-stone-800">
                            <li class="flex items-center justify-between gap-4 px-5 py-3.5">
                                <span class="text-sm text-stone-700 dark:text-stone-200">{{ __('Public registration') }}</span>
                                <span class="font-mono text-[11px] tracking-wide text-stone-500 uppercase">{{ __('No account') }}</span>
                            </li>
                            <li class="flex items-center justify-between gap-4 px-5 py-3.5">
                                <span class="text-sm text-stone-700 dark:text-stone-200">{{ __('Office review') }}</span>
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-400/15 dark:text-amber-300">{{ __('Pending') }}</span>
                            </li>
                            <li class="flex items-center justify-between gap-4 px-5 py-3.5">
                                <span class="text-sm text-stone-700 dark:text-stone-200">{{ __('Representative finalize') }}</span>
                                <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-800 dark:bg-sky-400/15 dark:text-sky-300">{{ __('Finalized') }}</span>
                            </li>
                            <li class="flex items-center justify-between gap-4 px-5 py-3.5">
                                <span class="text-sm text-stone-700 dark:text-stone-200">{{ __('Revision with remarks') }}</span>
                                <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-800 dark:bg-rose-400/15 dark:text-rose-300">{{ __('Required') }}</span>
                            </li>
                            <li class="flex items-center justify-between gap-4 bg-blue-50 px-5 py-3.5 dark:bg-blue-500/10">
                                <span class="text-sm font-medium text-stone-950 dark:text-white">{{ __('DICT package') }}</span>
                                <span class="rounded-full bg-emerald-600 px-2 py-0.5 text-[11px] font-semibold text-white">{{ __('Export') }}</span>
                            </li>
                        </ol>
                    </div>
                </aside>
            </div>
        </section>

        <section class="border-y border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">
            <div class="mx-auto grid max-w-6xl gap-px bg-stone-200 sm:grid-cols-3 dark:bg-stone-800">
                <div class="bg-white px-6 py-8 dark:bg-stone-900">
                    <p class="font-mono text-xs tracking-[0.18em] text-stone-500 uppercase">01</p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight">{{ __('Six named steps') }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600 dark:text-stone-400">{{ __('Registration, review, batch, return, approval, export. Each handoff is a named action.') }}</p>
                </div>
                <div class="bg-white px-6 py-8 dark:bg-stone-900">
                    <p class="font-mono text-xs tracking-[0.18em] text-stone-500 uppercase">02</p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight">{{ __('Three status layers') }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600 dark:text-stone-400">{{ __('Submission, batch, and application status stay separate so a group never hides a single record.') }}</p>
                </div>
                <div class="bg-white px-6 py-8 dark:bg-stone-900">
                    <p class="font-mono text-xs tracking-[0.18em] text-stone-500 uppercase">03</p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight">{{ __('One release package') }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600 dark:text-stone-400">{{ __('CSV plus attachments, produced only after admin marks the batch for submission.') }}</p>
                </div>
            </div>
        </section>

        <section id="pipeline" class="py-24">
            <div class="mx-auto grid max-w-6xl gap-12 px-6 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <p class="text-xs font-semibold tracking-[0.22em] text-blue-700 uppercase dark:text-blue-400">{{ __('Pipeline') }}</p>
                    <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">{{ __('The office decides what moves.') }}</h2>
                    <p class="mt-4 text-stone-600 dark:text-stone-300">{{ __('A shareable form opens the door. Finalizing a batch is what sends work upstairs. Export is the last door, and it stays shut until approval.') }}</p>
                </div>
                <ol class="flex flex-col gap-0 lg:col-span-8">
                    @foreach ([
                        ['01', __('Publish'), __('The representative activates a shareable form and issues the public link. Employees register without an account.')],
                        ['02', __('Register'), __('The applicant completes personal, address, and employment details, then uploads the PNPKI form and supporting IDs.')],
                        ['03', __('Finalize'), __('The representative checks the record, corrects it if needed, and finalizes it. Pending work stays editable.')],
                        ['04', __('Batch'), __('Finalized submissions are grouped. Finalizing the batch locks it and sends it for admin review.')],
                        ['05', __('Clear or return'), __('Admin marks records for submission or returns the batch. Every flag carries remarks.')],
                        ['06', __('Export'), __('Approved work leaves as a DICT package: CSV and the attachment archive.')],
                    ] as [$index, $title, $body])
                        <li class="grid grid-cols-[auto_1fr] gap-5 border-t border-stone-200 py-5 dark:border-stone-800">
                            <span class="font-mono text-sm text-stone-400">{{ $index }}</span>
                            <div>
                                <h3 class="text-lg font-semibold">{{ $title }}</h3>
                                <p class="mt-1 text-sm leading-relaxed text-stone-600 dark:text-stone-400">{{ $body }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <section id="roles" class="border-y border-stone-200 bg-stone-950 py-24 text-white dark:border-stone-800">
            <div class="mx-auto max-w-6xl px-6">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold tracking-[0.22em] text-blue-400 uppercase">{{ __('Authority') }}</p>
                    <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">{{ __('Three roles. Clear ownership.') }}</h2>
                </div>
                <div class="mt-12 grid gap-6 lg:grid-cols-3">
                    <article class="flex flex-col gap-4 border border-white/10 p-6">
                        <p class="font-mono text-xs tracking-[0.18em] text-stone-400 uppercase">{{ __('Applicant') }}</p>
                        <h3 class="text-2xl font-semibold tracking-tight">{{ __('Employee') }}</h3>
                        <p class="text-sm leading-relaxed text-stone-300">{{ __('Uses the shared link. Submits the form and documents. Holds no account and no review power.') }}</p>
                    </article>
                    <article class="flex flex-col gap-4 border border-white/10 bg-white/5 p-6">
                        <p class="font-mono text-xs tracking-[0.18em] text-blue-300 uppercase">{{ __('Office') }}</p>
                        <h3 class="text-2xl font-semibold tracking-tight">{{ __('Representative') }}</h3>
                        <p class="text-sm leading-relaxed text-stone-300">{{ __('Owns one office form. Reviews, corrects, finalizes submissions, builds the batch, and sends it up.') }}</p>
                    </article>
                    <article class="flex flex-col gap-4 border border-white/10 p-6">
                        <p class="font-mono text-xs tracking-[0.18em] text-stone-400 uppercase">{{ __('Release') }}</p>
                        <h3 class="text-2xl font-semibold tracking-tight">{{ __('Administrator') }}</h3>
                        <p class="text-sm leading-relaxed text-stone-300">{{ __('Reviews finalized batches, returns them with remarks, marks work for submission, and produces the DICT export.') }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="control" class="py-24">
            <div class="mx-auto max-w-6xl px-6">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold tracking-[0.22em] text-blue-700 uppercase dark:text-blue-400">{{ __('Control') }}</p>
                    <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">{{ __('Status is the system of record.') }}</h2>
                    <p class="mt-4 text-stone-600 dark:text-stone-300">{{ __('A submission, its batch, and the admin application each carry their own status. Flags never land without remarks.') }}</p>
                </div>
                <div class="mt-12 grid gap-6 lg:grid-cols-3">
                    <div class="rounded-2xl border border-stone-200 p-6 dark:border-stone-800">
                        <h3 class="text-lg font-semibold">{{ __('Submission') }}</h3>
                        <p class="mt-1 text-sm text-stone-500">{{ __('Per employee record') }}</p>
                        <ul class="mt-6 flex flex-col gap-3 text-sm">
                            <li class="flex items-center justify-between gap-3"><span>{{ __('Pending') }}</span><span class="text-stone-500">{{ __('Editable') }}</span></li>
                            <li class="flex items-center justify-between gap-3"><span>{{ __('Finalized') }}</span><span class="text-stone-500">{{ __('Office approved') }}</span></li>
                            <li class="flex items-center justify-between gap-3"><span>{{ __('Needs revision') }}</span><span class="text-stone-500">{{ __('Remarks required') }}</span></li>
                            <li class="flex items-center justify-between gap-3"><span>{{ __('For submission') }}</span><span class="text-stone-500">{{ __('Cleared') }}</span></li>
                        </ul>
                    </div>
                    <div class="rounded-2xl border border-stone-200 p-6 dark:border-stone-800">
                        <h3 class="text-lg font-semibold">{{ __('Batch') }}</h3>
                        <p class="mt-1 text-sm text-stone-500">{{ __('The grouped set') }}</p>
                        <ul class="mt-6 flex flex-col gap-3 text-sm">
                            <li class="flex items-center justify-between gap-3"><span>{{ __('Pending') }}</span><span class="text-stone-500">{{ __('Still assembling') }}</span></li>
                            <li class="flex items-center justify-between gap-3"><span>{{ __('Finalized') }}</span><span class="text-stone-500">{{ __('Locked for admin') }}</span></li>
                            <li class="flex items-center justify-between gap-3"><span>{{ __('Needs revision') }}</span><span class="text-stone-500">{{ __('Returned') }}</span></li>
                        </ul>
                    </div>
                    <div class="rounded-2xl border border-stone-950 bg-stone-950 p-6 text-white dark:border-white dark:bg-white dark:text-stone-950">
                        <h3 class="text-lg font-semibold">{{ __('Application') }}</h3>
                        <p class="mt-1 text-sm text-stone-400 dark:text-stone-500">{{ __('Admin workflow on a finalized batch') }}</p>
                        <p class="mt-6 text-sm leading-relaxed text-stone-300 dark:text-stone-600">{{ __('This is the release decision. Mark for submission, approve, or send the batch back. Export follows approval.') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="pb-24">
            <div class="mx-auto max-w-6xl px-6">
                <div class="flex flex-col items-start justify-between gap-8 rounded-2xl bg-blue-700 px-8 py-10 text-white sm:flex-row sm:items-center dark:bg-blue-600">
                    <div>
                        <h2 class="text-3xl font-semibold tracking-tight">{{ __('Open the console.') }}</h2>
                        <p class="mt-2 max-w-xl text-blue-100">{{ __('The pipeline is already defined. Sign in and work the queue that belongs to your role.') }}</p>
                    </div>
                    <a href="{{ route('filament.admin.auth.login') }}" class="inline-flex shrink-0 items-center justify-center rounded-md bg-white px-5 py-3 text-sm font-semibold text-blue-800 transition hover:bg-blue-50">
                        {{ __('Enter console') }}
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-stone-200 py-8 dark:border-stone-800">
        <div class="mx-auto flex max-w-6xl flex-col gap-2 px-6 text-sm text-stone-500 sm:flex-row sm:items-center sm:justify-between dark:text-stone-400">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
            <p>{{ __('PNPKI certificate operations') }}</p>
        </div>
    </footer>

</body>
</html>
