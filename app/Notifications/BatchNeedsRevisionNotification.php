<?php

namespace App\Notifications;

use App\Models\Batch;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BatchNeedsRevisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Batch $batch
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Batch returned for revision')
            ->body($this->batch->batch_name)
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->label('View Batch')
                    ->url(route('filament.admin.resources.batches.view', [
                        'record' => $this->batch->id,
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