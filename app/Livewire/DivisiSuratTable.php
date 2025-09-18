<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Surat;
use App\Models\StatusSurat;
use App\Models\Jabatan;
use App\Models\Disposisi;
use App\Models\Tracking;
use App\Models\User; // Import User model
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads; // For file uploads
use App\Notifications\DivisiRejectedSuratNotification;
use App\Notifications\SuratCompletedNotification; // Import Notification class

class DivisiSuratTable extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $sortField = 'tanggal_surat';
    public $sortDirection = 'desc';
    public $filterStatus = '';

    public $currentSuratId; // For modal
    public $keterangan; // For returnToKabag modal
    public $finalDocument; // For completeSurat modal

    protected $listeners = ['suratProcessed' => '$refresh'];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function render()
    {
        $user = Auth::user();
        $userJabatan = $user->jabatan->nama_jabatan ?? null;

        $prosesDivisiStatusId = StatusSurat::where('kode_status', 'proses_divisi')->first()->id ?? null;
        $selesaiStatusId = StatusSurat::where('kode_status', 'selesai')->first()->id ?? null;

        $suratsQuery = Surat::with(['jenisSurat', 'currentStatus', 'createdBy', 'tujuanJabatan']);

        // Divisi sees letters with 'proses_divisi' or 'selesai' status that are targeted to their jabatan
        if (str_contains($userJabatan, 'Divisi')) {
            $suratsQuery->where(function ($query) use ($user, $prosesDivisiStatusId, $selesaiStatusId) {
                $query->whereIn('status_id', [$prosesDivisiStatusId, $selesaiStatusId])
                      ->where('tujuan_jabatan_id', $user->jabatan_id); // Assuming disposisi to division updates tujuan_jabatan_id
            });
        } else {
            // If not a recognized Divisi role, return empty query
            $suratsQuery->whereRaw('1 = 0');
        }

        $surats = $suratsQuery
            ->when($this->search, function ($query) {
                $query->where('nomor_surat', 'like', '%'.$this->search.'%')
                      ->orWhere('perihal', 'like', '%'.$this->search.'%')
                      ->orWhereHas('createdBy', fn($q) => $q->where('nama', 'like', '%'.$this->search.'%'));
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status_id', $this->filterStatus);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $allStatuses = StatusSurat::all();

        return view('livewire.divisi-surat-table', [
            'surats' => $surats,
            'allStatuses' => $allStatuses,
        ]);
    }

    public function archiveSurat($suratId)
    {
        $user = Auth::user();
        $surat = Surat::findOrFail($suratId);
        $selesaiStatus = StatusSurat::where('kode_status', 'selesai')->firstOrFail();

        if ($surat->status_id !== $selesaiStatus->id) {
            session()->flash('error', 'Hanya surat yang sudah selesai yang bisa diarsipkan.');
            return;
        }

        DB::beginTransaction();
        try {
            $arsipStatusId = StatusSurat::where('kode_status', 'arsip')->firstOrFail()->id;

            $surat->update([
                'status_id' => $arsipStatusId,
                'updated_by' => $user->id,
            ]);

            Tracking::create([
                'surat_id' => $surat->id,
                'user_id' => $user->id,
                'action' => 'archived',
                'keterangan' => 'Surat diarsipkan oleh ' . $user->nama,
                'data_after' => $surat->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();
            session()->flash('success', 'Surat berhasil diarsipkan.');
            $this->dispatch('suratProcessed');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal mengarsipkan surat: ' . $e->getMessage());
            Log::error('Error archiving surat: ' . $e->getMessage());
        }
    }

    public function completeSurat($suratId)
    {
        $user = Auth::user();
        $surat = Surat::findOrFail($suratId);

        $this->validate([
            'finalDocument' => 'required|file|mimes:pdf|max:10240', // 10MB Max
        ]);

        DB::beginTransaction();
        try {
            $selesaiStatusId = StatusSurat::where('kode_status', 'selesai')->firstOrFail()->id;

            // Upload file
            $filePath = $this->finalDocument->store('surat_final', 'public');

            $surat->update([
                'status_id' => $selesaiStatusId,
                'file_surat' => $filePath, // Update with final document path
                'updated_by' => $user->id,
            ]);

            Tracking::create([
                'surat_id' => $surat->id,
                'user_id' => $user->id,
                'action' => 'completed',
                'keterangan' => 'Surat diselesaikan oleh ' . $user->nama . ' dengan dokumen final.',
                'data_after' => $surat->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Notify the creator of the surat
            $surat->createdBy->notify(new SuratCompletedNotification($surat));

            DB::commit();
            session()->flash('success', 'Surat berhasil diselesaikan dan dokumen final diunggah.');
            $this->dispatch('suratProcessed');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyelesaikan surat: ' . $e->getMessage());
            Log::error('Error completing surat: ' . $e->getMessage());
        }

        $this->reset(['currentSuratId', 'finalDocument']);
    }

    public function returnToKabag($suratId, $keterangan = null)
    {
        $user = Auth::user();
        $surat = Surat::findOrFail($suratId);

        $this->validate([
            'keterangan' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $ditolakDivisiStatusId = StatusSurat::where('kode_status', 'ditolak_divisi')->firstOrFail()->id;

            $surat->update([
                'status_id' => $ditolakDivisiStatusId,
                'updated_by' => $user->id,
            ]);

            Tracking::create([
                'surat_id' => $surat->id,
                'user_id' => $user->id,
                'action' => 'returned_to_kabag',
                'keterangan' => 'Surat dikembalikan ke Kabag TU oleh ' . $user->nama . ': ' . $keterangan,
                'data_after' => $surat->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Notify Kabag TU users
            $kabagTuJabatan = Jabatan::where('nama_jabatan', 'Kepala Bagian TU')->first();
            if ($kabagTuJabatan) {
                $kabagTuUsers = User::where('jabatan_id', $kabagTuJabatan->id)->get();
                foreach ($kabagTuUsers as $kabagUser) {
                    $kabagUser->notify(new DivisiRejectedSuratNotification($surat, $keterangan));
                }
            }

            DB::commit();
            session()->flash('success', 'Surat berhasil dikembalikan ke Kabag TU.');
            $this->dispatch('suratProcessed');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal mengembalikan surat: ' . $e->getMessage());
            Log::error('Error returning surat to Kabag: ' . $e->getMessage());
        }

        $this->reset(['currentSuratId', 'keterangan']);
    }
}