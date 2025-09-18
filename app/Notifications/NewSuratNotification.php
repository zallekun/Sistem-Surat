<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Surat; // Import Surat model
use App\Models\Notification as CustomNotification; // Alias custom Notification model

class NewSuratNotification extends Notification
{
    use Queueable;

    public $surat; // Public property to hold the Surat object

    /**
     * Create a new notification instance.
     */
    public function __construct(Surat $surat)
    {
        $this->surat = $surat;
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
            'message' => 'Surat baru dengan perihal "' . $this->surat->perihal . '" menunggu verifikasi Anda.',
            'link' => route('staff.surat.show', $this->surat->id),
            'type' => 'new_surat',
        ];
    }
}