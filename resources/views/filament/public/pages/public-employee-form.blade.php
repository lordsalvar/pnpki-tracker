<x-filament-panels::page>
    @if ($this->submitted)

    <div class="flex items-start justify-center px-4 pb-16 pt-10">
        <div class="w-full max-w-2xl">

            {{-- Main Card --}}
            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">

                {{-- Top accent strip --}}
                <div class="h-2 w-full bg-gradient-to-r from-primary-600 via-primary-400 to-success-500"></div>

                {{-- Body --}}
                <div class="px-14 py-12">

                    {{-- Success Badge --}}
                    <div class="mb-8 flex items-center gap-6">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-success-50 ring-8 ring-success-100 dark:bg-success-950 dark:ring-success-900">
                            <x-heroicon-s-check-circle class="h-12 w-12 text-success-600 dark:text-success-400" />
                        </div>
                        <div>
                            <h2 class="text-3xl font-extrabold leading-snug text-gray-900 dark:text-white">
                                Submission Complete
                            </h2>
                            <p class="mt-1 text-base text-gray-500 dark:text-gray-400">
                                Your information was received successfully.
                            </p>
                        </div>
                    </div>

                    {{-- Receipt copy prompt --}}
                    <div class="mb-10 flex flex-col gap-4 rounded-xl border border-blue-100 bg-blue-50 px-5 py-4 dark:border-blue-900/40 dark:bg-blue-950/30 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <x-heroicon-o-document-text class="mt-0.5 h-6 w-6 shrink-0 text-blue-500 dark:text-blue-400" />
                            <p class="text-base leading-relaxed text-blue-700 dark:text-blue-300">
                                Would you like a PDF copy of your responses for your records? Use the button to open or save it.
                            </p>
                        </div>
                        <div class="shrink-0 sm:ml-4">
                            <x-filament::button wire:click="downloadReceiptCopy" color="primary">
                                Download copy
                            </x-filament::button>
                        </div>
                    </div>

                    {{-- Divider with label --}}
                    <div class="relative mb-10">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-dashed border-gray-200 dark:border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="bg-white px-5 text-sm font-semibold uppercase tracking-[0.18em] text-gray-400 dark:bg-gray-900 dark:text-gray-500">
                                Share Feedback
                            </span>
                        </div>
                    </div>

                    {{-- Feedback Section --}}
                    <div class="flex items-center gap-8">

                        {{-- QR Code Tile --}}
                        <div class="shrink-0 rounded-2xl border border-gray-200 bg-gray-50 p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <img
                                src="{{ asset('images/marck.jpg') }}"
                                alt="PGO-PICTO feedback QR code"
                                class="h-32 w-32 rounded-xl object-contain"
                                loading="lazy"
                                decoding="async"
                            />
                        </div>

                        {{-- CTA Text --}}
                        <div class="flex-1 space-y-3">
                            <div class="flex items-center gap-2.5">
                                <x-heroicon-s-chat-bubble-left-ellipsis class="h-6 w-6 text-primary-500" />
                                <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                                    Rate Your Experience
                                </h3>
                            </div>
                            <p class="text-base leading-relaxed text-gray-500 dark:text-gray-400">
                                Scan the QR code to open the feedback form. Your input helps us serve you better.
                            </p>
                            <p class="text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                                Can't scan the code?
                                <br>
                                <a
                                    href="https://tinyurl.com/pictoform"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="break-all font-semibold text-primary-600 underline decoration-primary-600/40 underline-offset-2 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                                >https://tinyurl.com/pictoform</a>
                            </p>
                        </div>

                    </div>

                </div>

                {{-- Footer --}}
                <div class="border-t border-gray-100 bg-gray-50 px-14 py-5 dark:border-gray-800 dark:bg-gray-800/40">
                    <p class="text-center text-sm font-medium tracking-wide text-gray-400 dark:text-gray-500">
                        PGO &ndash; Provincial Information and Communications Technology Office
                    </p>
                </div>

            </div>

        </div>
    </div>

    @else
        <form wire:submit="submit">
            {{ $this->form }}

            <div class="mt-6" wire:ignore>
                <div
                    class="cf-turnstile"
                    data-sitekey="{{ config('services.turnstile.site_key') }}"
                    data-callback="onTurnstileSolved"
                    data-expired-callback="onTurnstileExpired"
                    data-error-callback="onTurnstileExpired"
                    data-theme="auto"
                ></div>
            </div>

            <div class="mt-4">
                <x-filament::button type="submit" size="lg">
                    Submit
                </x-filament::button>
            </div>
        </form>

        <script>
            function onTurnstileSolved(token) {
                @this.set('captchaToken', token);
            }
            function onTurnstileExpired() {
                @this.set('captchaToken', null);
            }
        </script>
    @endif
</x-filament-panels::page>
