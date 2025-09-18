<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Surat; // Import Surat model
use App\Models\Jabatan; // Import Jabatan model

class NewDisposisiNotification extends Notification
{
    use Queueable;

    public $surat;
    public $instruksi;
    public $tujuanJabatan;

    /**
     * Create a new notification instance.
     */
    public function __construct(Surat $surat, string $instruksi, Jabatan $tujuanJabatan)
    {
        $this->surat = $surat;
        $this->instruksi = $instruksi;
        $this->tujuanJabatan = $tujuanJabatan;
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
            'message' => 'Surat dengan perihal "' . $this->surat->perihal . '" telah didisposisi kepada Anda. Instruksi: ' . $this->instruksi,
            'link' => route('pimpinan.surat.disposisi'), // Link to Pimpinan's surat list
            'type' => 'new_disposisi',
            'instruksi' => $this->instruksi,
            'dari_jabatan' => Auth::user()->jabatan->nama_jabatan ?? 'N/A',
            'tujuan_jabatan' => $this->tujuanJabatan->nama_jabatan,
        ];
    }
}