<?php

namespace App\Filament\Support;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordConfirmation
{
    /** @return array<int, TextInput> */
    public static function schema(): array
    {
        return [
            TextInput::make('password')
                ->label('Password')
                ->password()
                ->revealable()
                ->required(),
        ];
    }

    public static function before(): Closure
    {
        return function (array $data, Action $action): void {
            if (! Hash::check($data['password'] ?? '', Auth::user()?->password ?? '')) {
                Notification::make()
                    ->title('Incorrect password.')
                    ->danger()
                    ->send();

                $action->halt();
            }
        };
    }
}
