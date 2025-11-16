<?php

namespace App\Notifications;

use App\Models\SantriHealthAction;
use App\Models\SantriHealthLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SantriHealthLogStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected SantriHealthLog $log,
        protected SantriHealthAction $action
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $santri = $this->log->santri?->nama ?? 'Santri';
        $status = ucfirst($this->log->status);

        return [
            'title' => "Update penanganan kesehatan: {$santri}",
            'body' => "{$santri} sekarang berstatus {$status}. Tindakan: {$this->action->tindakan}.",
            'log_id' => $this->log->id,
            'status' => $this->log->status,
            'tindakan' => $this->action->tindakan,
            'catatan' => $this->action->catatan,
        ];
    }
}
