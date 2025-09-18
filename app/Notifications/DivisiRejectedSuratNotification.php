<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Surat; // Import Surat model

class DivisiRejectedSuratNotification extends Notification
{
    use Queueable;

    public $surat;
    public $keterangan;

    /**
     * Create a new notification instance.
     */
    public function __construct(Surat $surat, string $keterangan = null)
    {
        $this->surat = $surat;
        $this->keterangan = $keterangan;
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
        $message = 'Surat dengan perihal "' . $this->surat->perihal . '" telah dikembalikan oleh Divisi.';
        if ($this->keterangan) {
            $message .= ' Alasan: ' . $this->keterangan;
        }

        return [
            'surat_id' => $this->surat->id,
            'nomor_surat' => $this->surat->nomor_surat,
            'perihal' => $this->surat->perihal,
            'message' => $message,
            'link' => route('kabag.surat.index'), // Link to Kabag TU's surat list
            'type' => 'divisi_rejected_surat',
            'keterangan' => $this->keterangan,
        ];
    }
}