<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Surat; // Import Surat model

class SuratCompletedNotification extends Notification
{
    use Queueable;

    public $surat;

    /**
     * Create a new notification instance.
     */
    public function __construct(Surat $surat)
    {
        $this->surat = $surat;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'surat_id' => $this->surat->id,
            'nomor_surat' => $this->surat->nomor_surat,
            'perihal' => $this->surat->perihal,
            'message' => 'Surat Anda dengan perihal "' . $this->surat->perihal . '" telah selesai diproses.',
            'link' => route('staff.surat.show', $this->surat->id),
            'type' => 'surat_completed',
        ];
    }
}