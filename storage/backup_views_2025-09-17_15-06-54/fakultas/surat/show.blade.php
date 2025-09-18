{{-- resources/views/fakultas/surat/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Surat Fakultas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detail Surat Fakultas</h5>
                    <a href="{{ route('fakultas.surat.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Basic Surat Info --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nomor Surat:</strong></td>
                                    <td>{{ $surat->nomor_surat ?? 'Belum ada nomor' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Perihal:</strong></td>
                                    <td>{{ $surat->perihal }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Surat:</strong></td>
                                    <td>{{ $surat->jenisSurat->nama_jenis ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Dibuat:</strong></td>
                                    <td>{{ $surat->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Dibuat Oleh:</strong></td>
                                    <td>{{ $surat->createdBy->nama ?? $surat->createdBy->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Prodi:</strong></td>
                                    <td>{{ $surat->prodi->nama_prodi ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Fakultas:</strong></td>
                                    <td>{{ $surat->prodi->fakultas->nama_fakultas ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status Saat Ini:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $surat->currentStatus->kode_status === 'disetujui_kaprodi' ? 'warning' : ($surat->currentStatus->kode_status === 'disetujui_fakultas' ? 'success' : 'secondary') }}">
                                            {{ $surat->currentStatus->nama_status ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Content --}}
                    @if($surat->isi_surat)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6>Isi Surat:</h6>
                            <div class="border p-3 bg-light rounded">
                                {!! nl2br(e($surat->isi_surat)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Actions for Faculty Staff --}}
                    @if($surat->currentStatus->kode_status === 'disetujui_kaprodi')
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6>Tindakan Fakultas:</h6>
                            <div class="btn-group" role="group">
                                <form action="{{ route('fakultas.surat.approve', $surat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menyetujui surat ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                </form>
                                
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#prosesModal">
                                    <i class="fas fa-hourglass-half"></i> Proses
                                </button>
                                
                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#tolakModal">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Status History --}}
                    @if(isset($surat->statusHistories) && $surat->statusHistories->count() > 0)
                    <div class="row">
                        <div class="col-12">
                            <h6>Riwayat Status:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Status</th>
                                            <th>Oleh</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($surat->statusHistories->sortByDesc('created_at') as $history)
                                        <tr>
                                            <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $history->status->nama_status ?? 'N/A' }}</td>
                                            <td>{{ $history->user->nama ?? $history->user->name ?? 'System' }}</td>
                                            <td>{{ $history->keterangan ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Proses --}}
<div class="modal fade" id="prosesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('fakultas.surat.updateStatus', $surat->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="diproses_fakultas">
                
                <div class="modal-header">
                    <h5 class="modal-title">Proses Surat</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="catatan">Catatan (opsional):</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Proses Surat</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Tolak --}}
<div class="modal fade" id="tolakModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('fakultas.surat.reject', $surat->id) }}" method="POST">
                @csrf
                
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Surat</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="keterangan">Alasan Penolakan: <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Jelaskan alasan penolakan..." required></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Surat</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection