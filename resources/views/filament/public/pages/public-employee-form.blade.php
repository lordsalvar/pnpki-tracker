<x-filament-panels::page>
    @if ($this->submitted)
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-success-100">
                <x-heroicon-o-check-circle class="h-10 w-10 text-success-600" />
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Thank You!</h2>
            <p class="mt-2 max-w-md text-gray-500 dark:text-gray-400">
                Your information has been submitted successfully. You may now close this page.
            </p>
        </div>
    @else
        <form
            wire:submit="submit"
            x-data="{ showCaptchaError: false, captchaToken: $wire.entangle('captchaToken') }"
            x-on:submit="if (!captchaToken) { showCaptchaError = true; $event.preventDefault(); }"
            x-on:turnstile-solved.window="showCaptchaError = false"
        >
            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-danger-200 bg-danger-50 p-4 text-danger-700 dark:border-danger-700/60 dark:bg-danger-900/30 dark:text-danger-200">
                    <p class="font-semibold">Please review the highlighted fields before submitting.</p>
                    <ul class="mt-2 list-disc space-y-1 ps-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $this->form }}

            {{-- Cloudflare Turnstile CAPTCHA --}}
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
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Complete CAPTCHA verification before submitting the form.
            </p>
            <p x-cloak x-show="showCaptchaError" class="mt-2 text-sm font-medium text-danger-600 dark:text-danger-400">
                Please complete the CAPTCHA before submitting.
            </p>
            @error('captchaToken')
                <p class="mt-2 text-sm font-medium text-danger-600 dark:text-danger-400">
                    {{ $message }}
                </p>
            @enderror

            <div class="mt-4">
                <x-filament::button type="submit" size="lg" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Submit</span>
                    <span wire:loading wire:target="submit">Submitting...</span>
                </x-filament::button>
            </div>
        </form>

        <script>
            function onTurnstileSolved(token) {
                @this.set('captchaToken', token);
                window.dispatchEvent(new CustomEvent('turnstile-solved'));
            }
            function onTurnstileExpired() {
                @this.set('captchaToken', null);
                window.dispatchEvent(new CustomEvent('turnstile-expired'));
            }
        </script>
    @endif
</x-filament-panels::page>
