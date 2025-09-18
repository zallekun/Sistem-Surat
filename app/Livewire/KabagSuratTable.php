<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Surat;
use App\Models\StatusSurat;
use App\Models\Jabatan;
use App\Models\Disposisi;
use App\Models\Tracking;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KabagSuratTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'tanggal_surat';
    public $sortDirection = 'desc';
    public $filterStatus = '';
    public $filterTujuanJabatan = ''; // For filtering by target division

    public $currentSuratId; // For modal
    public $instruksi; // For modal
    public $tujuanDivisiId; // For modal

    protected $listeners = ['suratDistributed' => '$refresh'];

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

        $disposisiKabagStatusId = StatusSurat::where('kode_status', 'disposisi_kabag')->first()->id ?? null;
        $prosesDivisiStatusId = StatusSurat::where('kode_status', 'proses_divisi')->first()->id ?? null;
        $ditolakDivisiStatusId = StatusSurat::where('kode_status', 'ditolak_divisi')->first()->id ?? null;


        $suratsQuery = Surat::with(['jenisSurat', 'currentStatus', 'createdBy', 'tujuanJabatan']);

        // Kabag TU sees letters with 'disposisi_kabag' status
        if ($userJabatan === 'Kepala Bagian TU') {
            $suratsQuery->where(function ($query) use ($disposisiKabagStatusId, $ditolakDivisiStatusId) {
                $query->where('status_id', $disposisiKabagStatusId)
                      ->orWhere('status_id', $ditolakDivisiStatusId);
            });
        } else {
            // If not Kabag TU, return empty query
            $suratsQuery->whereRaw('1 = 0');
        }

        $surats = $suratsQuery
            ->when($this->search, function ($query) {
                $query->where('nomor_surat', 'like', '%'.$this->search.'%')
                      ->orWhere('perihal', 'like', '%'.$this->search.'%')
                      ->orWhereHas('createdBy', fn($q) => $q->where('nama', 'like', '%'.$this->search.'%'))
                      ->orWhereHas('tujuanJabatan', fn($q) => $q->where('nama_jabatan', 'like', '%'.$this->search.'%'));
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status_id', $this->filterStatus);
            })
            ->when($this->filterTujuanJabatan, function ($query) {
                // Filter by the final destination of the surat, which might be a division
                $query->where('tujuan_jabatan_id', $this->filterTujuanJabatan);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $allStatuses = StatusSurat::all();
        $tujuanDivisis = Jabatan::whereIn('nama_jabatan', [
            'Divisi Akademik', 'Divisi Keuangan', 'Divisi Kerjasama', 'Divisi Kemahasiswaan', 'Divisi Umum & Perlengkapan'
        ])->get();

        return view('livewire.kabag-surat-table', [
            'surats' => $surats,
            'allStatuses' => $allStatuses,
            'tujuanDivisis' => $tujuanDivisis,
        ]);
    }

    public function distributeSurat($suratId, $instruksi, $tujuanDivisiId)
    {
        $user = Auth::user();
        $surat = Surat::findOrFail($suratId);

        $this->validate([
            'instruksi' => 'required|string|max:500',
            'tujuanDivisiId' => 'required|exists:jabatan,id',
        ]);

        DB::beginTransaction();
        try {
            $prosesDivisiStatusId = StatusSurat::where('kode_status', 'proses_divisi')->firstOrFail()->id;

            // Create Disposisi record
            Disposisi::create([
                'surat_id' => $surat->id,
                'dari_user' => $user->id,
                'kepada_jabatan_id' => $tujuanDivisiId,
                'instruksi' => $instruksi,
            ]);

            // Update Surat status
            $surat->update([
                'status_id' => $prosesDivisiStatusId,
                'updated_by' => $user->id,
            ]);

            // Log Tracking
            Tracking::create([
                'surat_id' => $surat->id,
                'user_id' => $user->id,
                'action' => 'distributed',
                'keterangan' => 'Surat didistribusikan oleh Kabag TU ' . $user->nama . ' kepada ' . Jabatan::find($tujuanDivisiId)->nama_jabatan . ': ' . $instruksi,
                'data_after' => $surat->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();
            session()->flash('success', 'Surat berhasil didistribusikan.');
            $this->dispatch('suratDistributed');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal mendistribusikan surat: ' . $e->getMessage());
            Log::error('Error distributing surat: ' . $e->getMessage());
        }

        // Reset modal fields
        $this->reset(['currentSuratId', 'instruksi', 'tujuanDivisiId']);
    }
}