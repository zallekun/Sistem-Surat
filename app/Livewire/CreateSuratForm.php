<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Surat;
use App\Models\Prodi;
use App\Models\Fakultas;
use App\Models\JenisSurat;
use App\Models\StatusSurat;
use App\Models\Jabatan;
use App\Models\User; // Import User model
use App\Models\Notification as CustomNotification; // Import custom Notification model
use App\Traits\HasNomorSurat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use App\Notifications\NewSuratNotification; // Import Notification class

class CreateSuratForm extends Component
{
    use WithFileUploads, HasNomorSurat;

    // Form properties
    public $nomor_surat;
    public $perihal;
    public $tujuan_jabatan_id;
    public $lampiran;
    public $prodi_id;
    public $fakultas_id;
    public $tanggal_surat;
    public $sifat_surat;
    public $file_surat;

    // Display properties for fixed fields
    public $fakultas_name;
    public $prodi_name;

    // Control properties
    public $isStaffFakultas = false;

    // Data for dropdowns
    public $sifatSuratOptions = ['Biasa', 'Segera', 'Rahasia'];
    public $tujuanJabatanOptions;

    public function mount()
    {
        $user = Auth::user()->load('jabatan', 'prodi.fakultas');

        $this->tanggal_surat = now()->format('Y-m-d');

        $this->tujuanJabatanOptions = Jabatan::whereIn('nama_jabatan', [
            'Dekan', 'Wakil Dekan Bidang Akademik', 'Wakil Dekan Bidang Keuangan', 'Wakil Dekan Bidang Kemahasiswaan', 'Kepala Bagian TU'
        ])->get();

        if ($user->jabatan?->nama_jabatan === 'Staff Program Studi' && $user->prodi) {
            $this->prodi_id = $user->prodi->id;
            $this->fakultas_id = $user->prodi->fakultas_id;
            $this->prodi_name = $user->prodi->nama_prodi;
            $this->fakultas_name = $user->prodi->fakultas->nama_fakultas;
        } elseif ($user->jabatan?->nama_jabatan === 'Staff Fakultas' && $user->prodi?->fakultas) {
            $this->isStaffFakultas = true;
            $this->prodi_id = null; // Staff fakultas does not have a prodi
            $this->fakultas_id = $user->prodi->fakultas->id;
            $this->fakultas_name = $user->prodi->fakultas->nama_fakultas;
        }

        if ($this->fakultas_id) {
            $this->generateNumber();
        }
    }

    public function generateNumber()
    {
        $this->nomor_surat = $this->generateNomorSurat($this->fakultas_id, $this->prodi_id, now()->year);
    }

    protected function rules()
    {
        $rules = [
            'perihal' => 'required|string|max:255',
            'tujuan_jabatan_id' => 'required|exists:jabatan,id',
            'lampiran' => 'nullable|string|max:255',
            'fakultas_id' => 'required|exists:fakultas,id',
            'tanggal_surat' => 'required|date',
            'sifat_surat' => 'required|in:Biasa,Segera,Rahasia',
            'file_surat' => 'required|file|mimes:pdf|max:10240',
        ];

        if (!$this->isStaffFakultas) {
            $rules['prodi_id'] = 'required|exists:prodi,id';
        }

        return $rules;
    }

    public function saveSurat()
    {
        $this->validate();

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $filePath = null;
            if ($this->file_surat) {
                $filePath = $this->file_surat->store('surat_pdfs', 'public');
            }

            $draftStatus = StatusSurat::where('kode_status', 'draft')->firstOrFail();
            $jenisSuratDefault = JenisSurat::first();

            $surat = Surat::create([
                'nomor_surat' => $this->nomor_surat,
                'perihal' => $this->perihal,
                'tujuan_jabatan_id' => $this->tujuan_jabatan_id,
                'lampiran' => $this->lampiran,
                'prodi_id' => $this->prodi_id, // Will be null for Staff Fakultas
                'fakultas_id' => $this->fakultas_id,
                'jenis_id' => $jenisSuratDefault ? $jenisSuratDefault->id : null,
                'status_id' => $draftStatus->id,
                'created_by' => $user->id,
                'tanggal_surat' => $this->tanggal_surat,
                'sifat_surat' => $this->sifat_surat,
                'file_surat' => $filePath,
            ]);

            // Send notification to Staff Fakultas (Bagian Umum)
            $staffFakultasJabatan = Jabatan::where('nama_jabatan', 'Staff Fakultas')->first();
            if ($staffFakultasJabatan) {
                $staffFakultasUsers = User::where('jabatan_id', $staffFakultasJabatan->id)->get();
                foreach ($staffFakultasUsers as $staffUser) {
                    $notification = new NewSuratNotification($surat);
                    $notificationData = $notification->toArray($staffUser);

                    CustomNotification::create([
                        'user_id' => $staffUser->id,
                        'type' => $notificationData['type'],
                        'title' => $notificationData['perihal'],
                        'message' => $notificationData['message'],
                        'data' => $notificationData,
                        'url' => $notificationData['link'],
                    ]);
                }
            }

            DB::commit();
            session()->flash('success', 'Surat berhasil dibuat!');
            return redirect()->route('staff.surat.show', $surat->id);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            session()->flash('error', 'Gagal membuat surat: ' . $e->getMessage());
        }
    }

    
    public function saveDraft()
    {
        $this->validate();

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $filePath = null;
            if ($this->file_surat) {
                $filePath = $this->file_surat->store('surat_pdfs', 'public');
            }

            $draftStatus = StatusSurat::where('kode_status', 'draft')->firstOrFail();
            $jenisSuratDefault = JenisSurat::first();

            $surat = Surat::create([
                'nomor_surat' => $this->nomor_surat,
                'perihal' => $this->perihal,
                'tujuan_jabatan_id' => $this->tujuan_jabatan_id,
                'lampiran' => $this->lampiran,
                'prodi_id' => $this->prodi_id,
                'fakultas_id' => $this->fakultas_id,
                'jenis_id' => $jenisSuratDefault ? $jenisSuratDefault->id : null,
                'status_id' => $draftStatus->id,
                'created_by' => $user->id,
                'tanggal_surat' => $this->tanggal_surat,
                'sifat_surat' => $this->sifat_surat,
                'file_surat' => $filePath,
            ]);

            DB::commit();
            session()->flash('success', 'Surat berhasil disimpan sebagai draft!');
            return redirect()->route('staff.surat.show', $surat->id);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            session()->flash('error', 'Gagal menyimpan draft: ' . $e->getMessage());
        }
    }

    public function confirmSubmit()
    {
        // Emit browser event for confirmation
        $this->dispatch('show-submit-confirmation');
    }

    public function submitForReview()
    {
        $this->validate();

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $filePath = null;
            if ($this->file_surat) {
                $filePath = $this->file_surat->store('surat_pdfs', 'public');
            }

            // Use review_kaprodi status instead of draft
            $reviewStatus = StatusSurat::where('kode_status', 'review_kaprodi')->first();
            if (!$reviewStatus) {
                $reviewStatus = StatusSurat::where('kode_status', 'diajukan')->first();
            }
            if (!$reviewStatus) {
                $reviewStatus = StatusSurat::where('kode_status', 'draft')->firstOrFail();
            }
            
            $jenisSuratDefault = JenisSurat::first();

            $surat = Surat::create([
                'nomor_surat' => $this->nomor_surat,
                'perihal' => $this->perihal,
                'tujuan_jabatan_id' => $this->tujuan_jabatan_id,
                'lampiran' => $this->lampiran,
                'prodi_id' => $this->prodi_id,
                'fakultas_id' => $this->fakultas_id,
                'jenis_id' => $jenisSuratDefault ? $jenisSuratDefault->id : null,
                'status_id' => $reviewStatus->id,
                'created_by' => $user->id,
                'tanggal_surat' => $this->tanggal_surat,
                'sifat_surat' => $this->sifat_surat,
                'file_surat' => $filePath,
            ]);

            // Send notification to Kaprodi
            $kaprodiJabatan = Jabatan::where('nama_jabatan', 'Ketua Program Studi')->first();
            if ($kaprodiJabatan && $this->prodi_id) {
                $kaprodiUsers = User::where('jabatan_id', $kaprodiJabatan->id)
                    ->where('prodi_id', $this->prodi_id)
                    ->get();
                foreach ($kaprodiUsers as $kaprodiUser) {
                    $notification = new NewSuratNotification($surat);
                    $notificationData = $notification->toArray($kaprodiUser);

                    CustomNotification::create([
                        'user_id' => $kaprodiUser->id,
                        'type' => $notificationData['type'],
                        'title' => $notificationData['perihal'],
                        'message' => 'Surat baru memerlukan review Anda',
                        'data' => $notificationData,
                        'url' => $notificationData['link'],
                    ]);
                }
            }

            // Also send to Staff Fakultas
            $staffFakultasJabatan = Jabatan::where('nama_jabatan', 'Staff Fakultas')->first();
            if ($staffFakultasJabatan) {
                $staffFakultasUsers = User::where('jabatan_id', $staffFakultasJabatan->id)->get();
                foreach ($staffFakultasUsers as $staffUser) {
                    $notification = new NewSuratNotification($surat);
                    $notificationData = $notification->toArray($staffUser);

                    CustomNotification::create([
                        'user_id' => $staffUser->id,
                        'type' => $notificationData['type'],
                        'title' => $notificationData['perihal'],
                        'message' => $notificationData['message'],
                        'data' => $notificationData,
                        'url' => $notificationData['link'],
                    ]);
                }
            }

            DB::commit();
            session()->flash('success', 'Surat berhasil dikirim ke Kaprodi untuk review!');
            return redirect()->route('staff.surat.show', $surat->id);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            session()->flash('error', 'Gagal mengirim surat: ' . $e->getMessage());
        }
    }

public function render()
    {
        return view('livewire.create-surat-form');
    }
}