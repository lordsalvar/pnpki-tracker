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
        <form wire:submit="submit">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit" size="lg">
                    Submit
                </x-filament::button>
            </div>
        </form>
    @endif
</x-filament-panels::page>
