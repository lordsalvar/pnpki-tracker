<?php

namespace App\Notifications;

use App\Models\FormSubmission;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFormSubmissionNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected FormSubmission $formSubmission
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('New submission entry')
            ->body(trim(
                $this->formSubmission->firstname.' '.
                $this->formSubmission->lastname
            ))
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->label('View Submission')
                    ->url(route('filament.admin.forms.resources.form-submissions.edit', [
                        'record' => $this->formSubmission->id,
                    ]))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
