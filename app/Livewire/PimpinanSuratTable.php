<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Surat;
use App\Models\StatusSurat;
use App\Models\Jabatan;
use App\Models\Disposisi;
use App\Models\Tracking;
use App\Models\User;
use App\Models\DisposisiParalel; // Import DisposisiParalel model
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewDisposisiNotification;

class PimpinanSuratTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'tanggal_surat';
    public $sortDirection = 'desc';
    public $filterStatus = '';
    public $filterTujuanJabatan = '';

    public $currentSuratId;
    public $instruksi;
    public $tujuanDisposisiId;
    public $selectedWds = []; // New property for parallel disposition

    protected $listeners = ['suratDisposed' => '$refresh', 'openDisposisiModal' => 'openDisposisiModal'];

    public function openDisposisiModal($suratId)
    {
        $this->currentSuratId = $suratId;
        $this->instruksi = '';
        $this->tujuanDisposisiId = '';
        $this->selectedWds = [];
        // No need to dispatch again, the listener already opened it
    }

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
        $userJabatanId = $user->jabatan_id;

        $suratsQuery = Surat::with(['jenisSurat', 'currentStatus', 'createdBy', 'tujuanJabatan', 'disposisiParalel'])
            ->where(function ($query) use ($userJabatanId) {
                // 1. Surat menunggu disposisi tunggal dari user ini
                $query->where(function ($q) use ($userJabatanId) {
                    $q->where('status_id', StatusSurat::where('kode_status', 'disposisi_pimpinan')->first()->id ?? null)
                      ->where('tujuan_jabatan_id', $userJabatanId);
                });

                // 2. Atau, surat dalam disposisi paralel yang ditujukan ke user ini dan belum selesai
                $query->orWhere(function ($q) use ($userJabatanId) {
                    $q->where('status_id', StatusSurat::where('kode_status', 'disposisi_paralel')->first()->id ?? null)
                      ->whereHas('disposisiParalel', function ($para) use ($userJabatanId) {
                          $para->whereJsonContains('kepada_jabatan_ids', $userJabatanId)
                               ->where(function ($sub) use ($userJabatanId) {
                                   $sub->whereJsonContains('status_per_jabatan->' . $userJabatanId, 'pending')
                                       ->orWhereRaw('JSON_EXTRACT(status_per_jabatan, ?) IS NULL', ['$."' . $userJabatanId . '"']);
                               });
                      });
                });
            });

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
                $query->where('tujuan_jabatan_id', $this->filterTujuanJabatan);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $allStatuses = StatusSurat::all();
        $tujuanJabatans = Jabatan::whereIn('nama_jabatan', [
            'Dekan', 'Wakil Dekan Bidang Akademik', 'Wakil Dekan Bidang Keuangan', 'Wakil Dekan Bidang Kemahasiswaan', 'Kepala Bagian TU', 'Kepala Program Studi', 'Divisi Akademik', 'Divisi Keuangan', 'Divisi Kerjasama', 'Divisi Kemahasiswaan', 'Divisi Umum & Perlengkapan'
        ])->get();

        // Get only Wakil Dekan for parallel disposition selection
        $wakilDekans = Jabatan::whereIn('nama_jabatan', [
            'Wakil Dekan Bidang Akademik', 'Wakil Dekan Bidang Keuangan', 'Wakil Dekan Bidang Kemahasiswaan'
        ])->get();

        return view('livewire.pimpinan-surat-table', [
            'surats' => $surats,
            'allStatuses' => $allStatuses,
            'tujuanJabatans' => $tujuanJabatans,
            'wakilDekans' => $wakilDekans, // Pass wakilDekans to the view
        ]);
    }

    public function disposeSurat() // Removed parameters as they are now properties
    {
        $user = Auth::user();
        $surat = Surat::findOrFail($this->currentSuratId);

        $this->validate([
            'instruksi' => 'required|string|max:500',
            'tujuanDisposisiId' => 'required_without:selectedWds|exists:jabatan,id',
            'selectedWds' => 'array|min:1|required_without:tujuanDisposisiId',
            'selectedWds.*' => 'exists:jabatan,id',
        ]);

        DB::beginTransaction();
        try {
            $nextStatusId = null;

            if ($user->jabatan->nama_jabatan === 'Dekan' && !empty($this->selectedWds)) {
                // Parallel disposition by Dekan
                $disposisiParalelStatusId = StatusSurat::where('kode_status', 'disposisi_paralel')->firstOrFail()->id;

                $kepadaJabatanIds = $this->selectedWds;
                $statusPerJabatan = [];
                foreach ($kepadaJabatanIds as $jabatanId) {
                    $statusPerJabatan[$jabatanId] = 'pending';
                }

                DisposisiParalel::create([
                    'surat_id' => $surat->id,
                    'dari_jabatan_id' => $user->jabatan_id,
                    'kepada_jabatan_ids' => $kepadaJabatanIds,
                    'status_per_jabatan' => $statusPerJabatan,
                ]);

                $nextStatusId = $disposisiParalelStatusId;

                // Create individual Disposisi records for each selected WD
                foreach ($kepadaJabatanIds as $wdJabatanId) {
                    $wdJabatan = Jabatan::findOrFail($wdJabatanId);
                    Disposisi::create([
                        'surat_id' => $surat->id,
                        'dari_user' => $user->id,
                        'kepada_jabatan_id' => $wdJabatanId,
                        'instruksi' => $this->instruksi,
                    ]);
                    // Notify each WD
                    $wdUsers = User::where('jabatan_id', $wdJabatanId)->get();
                    foreach ($wdUsers as $wdUser) {
                        $wdUser->notify(new NewDisposisiNotification($surat, $this->instruksi, $wdJabatan));
                    }
                }

                $keteranganLog = 'Surat didisposisi paralel oleh Dekan ' . $user->nama . ' kepada ' . implode(', ', Jabatan::whereIn('id', $kepadaJabatanIds)->pluck('nama_jabatan')->toArray()) . ': ' . $this->instruksi;

            } else {
                // Single disposition (existing logic)
                $tujuanJabatan = Jabatan::findOrFail($this->tujuanDisposisiId);

                if ($tujuanJabatan->nama_jabatan === 'Kepala Bagian TU') {
                    $nextStatusId = StatusSurat::where('kode_status', 'disposisi_kabag')->firstOrFail()->id;
                } elseif (in_array($tujuanJabatan->nama_jabatan, ['Dekan', 'Wakil Dekan Bidang Akademik', 'Wakil Dekan Bidang Keuangan', 'Wakil Dekan Bidang Kemahasiswaan'])) {
                    $nextStatusId = StatusSurat::where('kode_status', 'disposisi_pimpinan')->firstOrFail()->id;
                } elseif (str_contains($tujuanJabatan->nama_jabatan, 'Divisi')) {
                    $nextStatusId = StatusSurat::where('kode_status', 'proses_divisi')->firstOrFail()->id;
                } else {
                    throw new \Exception('Tujuan disposisi tidak valid.');
                }

                Disposisi::create([
                    'surat_id' => $surat->id,
                    'dari_user' => $user->id,
                    'kepada_jabatan_id' => $this->tujuanDisposisiId,
                    'instruksi' => $this->instruksi,
                ]);

                $keteranganLog = 'Surat didisposisi oleh ' . $user->nama . ' kepada ' . $tujuanJabatan->nama_jabatan . ': ' . $this->instruksi;

                // Notify the single recipient
                $tujuanUsers = User::where('jabatan_id', $this->tujuanDisposisiId)->get();
                foreach ($tujuanUsers as $tujuanUser) {
                    $tujuanUser->notify(new NewDisposisiNotification($surat, $this->instruksi, $tujuanJabatan));
                }
            }

            $surat->update([
                'status_id' => $nextStatusId,
                'updated_by' => $user->id,
            ]);

            Tracking::create([
                'surat_id' => $surat->id,
                'user_id' => $user->id,
                'action' => 'disposed',
                'keterangan' => $keteranganLog,
                'data_after' => $surat->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();
            session()->flash('success', 'Surat berhasil didisposisi.');
            $this->dispatch('suratDisposed');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal mendisposisi surat: ' . $e->getMessage());
            Log::error('Error disposing surat: ' . $e->getMessage());
        }

        $this->reset(['currentSuratId', 'instruksi', 'tujuanDisposisiId', 'selectedWds']);
    }

    // New method for WDs to complete their part of parallel disposition
    public function completeParallelDisposisi($suratId)
    {
        $user = Auth::user();
        $surat = Surat::findOrFail($suratId);

        // Find the parallel disposition entry for this surat
        $disposisiParalel = DisposisiParalel::where('surat_id', $suratId)
                                            ->whereJsonContains('kepada_jabatan_ids', $user->jabatan_id)
                                            ->firstOrFail();

        DB::beginTransaction();
        try {
            $statusPerJabatan = $disposisiParalel->status_per_jabatan;
            $statusPerJabatan[$user->jabatan_id] = 'completed';
            $disposisiParalel->status_per_jabatan = $statusPerJabatan;
            $disposisiParalel->save();

            // Check if all parallel dispositions are completed
            $allCompleted = true;
            foreach ($disposisiParalel->kepada_jabatan_ids as $jabatanId) {
                if (($disposisiParalel->status_per_jabatan[$jabatanId] ?? 'pending') !== 'completed') {
                    $allCompleted = false;
                    break;
                }
            }

            if ($allCompleted) {
                // All WDs have completed their part, now notify Kabag TU
                $kabagTuJabatan = Jabatan::where('nama_jabatan', 'Kepala Bagian TU')->first();
                if ($kabagTuJabatan) {
                    $kabagTuUsers = User::where('jabatan_id', $kabagTuJabatan->id)->get();
                    foreach ($kabagTuUsers as $kabagUser) {
                        $kabagUser->notify(new NewDisposisiNotification($surat, 'Semua Wakil Dekan telah menyelesaikan disposisi paralel untuk surat ini.', $kabagTuJabatan)); // Reusing NewDisposisiNotification for simplicity
                    }
                }
                // Optionally change surat status to indicate readiness for Kabag TU
                $surat->update(['status_id' => StatusSurat::where('kode_status', 'disposisi_kabag')->firstOrFail()->id]); // Assuming it goes to Kabag TU after parallel
            }

            Tracking::create([
                'surat_id' => $surat->id,
                'user_id' => $user->id,
                'action' => 'parallel_disposition_completed',
                'keterangan' => 'Disposisi paralel diselesaikan oleh ' . $user->nama . ' (' . $user->jabatan->nama_jabatan . ').',
                'data_after' => $surat->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();
            session()->flash('success', 'Disposisi paralel Anda berhasil diselesaikan.');
            $this->dispatch('suratDisposed');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyelesaikan disposisi paralel: ' . $e->getMessage());
            Log::error('Error completing parallel disposition: ' . $e->getMessage());
        }
    }

    // Placeholder for Tanda Tangan method (for Dekan)
    public function tandaTanganSurat($suratId)
    {
        $user = Auth::user();
        $surat = Surat::findOrFail($suratId);

        if ($user->jabatan->nama_jabatan !== 'Dekan' || $surat->currentStatus->kode_status !== 'menunggu_ttd_dekan') {
            session()->flash('error', 'Anda tidak memiliki hak untuk menandatangani surat ini atau status surat tidak sesuai.');
            return;
        }

        DB::beginTransaction();
        try {
            $selesaiStatusId = StatusSurat::where('kode_status', 'selesai')->firstOrFail()->id;

            $surat->update([
                'status_id' => $selesaiStatusId,
                'updated_by' => $user->id,
            ]);

            Tracking::create([
                'surat_id' => $surat->id,
                'user_id' => $user->id,
                'action' => 'signed',
                'keterangan' => 'Surat ditandatangani oleh Dekan ' . $user->nama,
                'data_after' => $surat->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();
            session()->flash('success', 'Surat berhasil ditandatangani.');
            $this->dispatch('suratDisposed');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menandatangani surat: ' . $e->getMessage());
            Log::error('Error signing surat: ' . $e->getMessage());
        }
    }
}